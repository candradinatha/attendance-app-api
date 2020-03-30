<?php

namespace App\Traits;

use Carbon\Carbon;

trait TableAddition {

    protected $defaultLimit = 10;
    protected $defaultInit  = [
        'limits' => [10, 20, 50],
        'searchTable' => ['name', 'description'],
        'paginate' => true,
    ];

    public function withTableAddition($request)
    {
        $query = $this->withTab($request)
                ->withWhere($request)
                ->withLevel()
                ->search($request)
                ->sort($request)
                ->withDateFilter($request)
                ->withRelation();

                // dd($query->toSql());
        return $this->getPaginate() ? 
                $query->paginate($this->setLimit($request)) : 
                $query->get();
    }

    public function scopeWithLevel($query)
    {
        if (!$levels = $this->getLevel()) return $query;

        foreach ($levels as $key => $value) {
            if (getGuard() == $key) {
                foreach ($value as $rel => $column) {
                    if ($rel != 'all') {
                        $user = Auth()->user()->{$column};
                        if (strpos($rel, '~')) {
                            $query->where($column, $user);
                        } else {
                            $query->whereHas($rel, function($model) use ($column, $user) {
                                $model->where($column, $user);
                            });
                        }
                    }
                }
            }
        }
    }


    public function scopeWithDateFilter($query, $request)
    {
        // dd($request->datefilter);
        if (!$request->datefilter) return $query;

        $date = decodeDateParam($request->datefilter);
        preg_match("!(\S+)\s*-\s*(\S+)!i", $date, $dates);
        $from = Carbon::parse($dates[1])->format('Y-m-d H:i:s');
        $to   = Carbon::parse($dates[2])->format('Y-m-d H:i:s');
        
        $query->whereBetween('start_time', [$from, $to]);
    }

    public function scopeWithTab($query, $request)
    {
        if (!$tab = $request->tab) {
            $tab = $this->getDefaultTab();
        }

        $tabOptions = $this->stringToArray($this->getWithTab());

        // dd($tabOptions);

        if (in_array($tab, array_keys($tabOptions))) {
            foreach ($tabOptions[$tab] as $key => $values) {
                // dump($key);

                if ($key == 'function') {
                    $q = 'return $query->getModel()->';

                    foreach ($values as $value) {
                        $q .= $value . '()->';
                    }

                    $q = substr($q, 0, -2) . ';';

                    return eval($q);
                } else {

                    foreach ($values as $value) {
                        if ($value === reset($values)) {
                            $query->where($key, $value);
                        } else {
                            $query->orWhere($key, $value);
                        }
                    }
                }
            }
        }
    }

    public function scopeWithWhere($query, $request)
    {
        if ($where = $this->getWhere()) {
            foreach ($where as $key => $columns) {
                if ($value = $request->{$key}) {
                $columns = explode(',', $columns);
                    foreach ($columns as $column) {
                            $query->orWhere($column, $value);
                    }
                }
            }
        }

        // foreach ($where as $key => $values) {
        //     foreach ($values as $value) {
        //         $query->orWhere($key, $value);
        //     }
        // }
    }

    public function scopeWithRelation($query)
    {
        if ($this->getRelations()) {
            $query->with($this->getRelations());
        }
    }

    public function scopeSearch($query, $request)
    {
        if (!$q = $request->search) return $query;
        
        $tables = $this->getSearchTable();
        
        foreach ($tables as $table) {
            
            if (strpos($table, '.') !== false) {
                $withRelation = explode('.', $table);
                $query->orWhereHas($withRelation[0], function ($search) use ($q, $withRelation) {
                    return $search->where($withRelation[1], 'LIKE', "%$q%");
                });

            } else {
                if ($table === reset($tables)) {
                    $query->where($table, 'LIKE', "%$q%");
                } else {
                    $query->orWhere($table, 'LIKE', "%$q%");
                }
            }
        }
    }

    public function scopeSort($query, $request)
    {
        if (!$sort = $request->sort) return $query;

        $sorts  = $this->getSort();
        $parent = $sorts[$sort];
        $order  = isset($parent['order']) ? $parent['order'] : 'DESC';

        $query->orderBy($parent['table'], $order);
    }

    protected function setLimit($request)
    {
        return in_array($request->limit, $this->getLimits()) ? $request->limit : $this->defaultLimit;
    }

    public function getLimits()
    {
        return $this->additional['limits'] ?? $this->defaultInit['limits'];
    }

    public function getSort()
    {
        return $this->additional['sort'];
    }

    public function getRelations()
    {
        return $this->additional['relation'] ?? null;
    }
    
    public function getWhere()
    {
        return $this->additional['where'] ?? null;
    }

    protected function getSearchTable()
    {
        return $this->additional['searchTable'];
    }

    protected function getWithTab()
    {
        return $this->additional['withTab'] ?? null;
    }

    protected function getDefaultTab()
    {
        return $this->additional['defaultTab'] ?? null;
    }

    protected function getLevel()
    {
        $level = $this->additional['level'] ?? null;

        $result = [];
        if (is_string($level)) {
            $toArray = $this->explodeString($level, '|');
            foreach ($toArray as $value) {
                $separate = $this->explodeString($value, ':');
                $val      = $this->explodeString($separate[1], '=');
                $result[$separate[0]] = [$val[0] => $val[1]];
            }
        }

        return $level ? $result : null;
    }

    protected function getPaginate()
    {
        return $this->additional['paginate'] ?? $this->defaultInit['paginate'];
    }

    private function stringToArray($string)
    {
        if (is_array($string)) return $string;

        $result = [];
        if (is_string($string)) {
            $toArray = $this->explodeString($string, '|');
            foreach ($toArray as $value) {

                if (strpos($value, ':')) {
                    preg_match("!(\S+)\s*:\s*(\S+)!i", $value, $match);

                    $fields = $this->explodeString($match[2], ';');
                    
                    foreach ($fields as $field) {
                        preg_match("!(\S+)\s*=\s*(\S+)!i", $field, $f);
                        $result[$match[1]][$f[1]] = explode(',', $f[2]);
                    }
                }

            }
        }

        return $result;
    }
    
    private function explodeString($string, $separator)
    {
        return is_string($string) ? explode($separator, $string) : $string;
    }
}