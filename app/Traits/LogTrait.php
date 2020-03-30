<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Log;

trait LogTrait {

    public static function bootLogTrait()
    {
        static::updating(function(Model $model) {
            $self = new self();
            $model->load($self->relations());

            $component = $self->components($model);
            $dirtyKey  = $self->dirtyKeys();

            $messages  = implode(', ', $self->checkDirty($model, $dirtyKey, $component));
            
            if ($messages) {
                $replacer   = Auth()->user();
                $replacerId = $replacer->username;
                // $level      = $replacer->getTable();

                $log = new Log();
                $log->id_data    = $model->id;
                $log->type       = $model->getTable();
                $log->message    = $messages;
                $log->replacer   = $replacerId;
                $log->level      = class_basename($replacer);
                
                $log->save();
            }
        });
    }

    private function checkDirty($model, $dirtyKeys, $components)
    {
        $messages = [];
        
        foreach ($dirtyKeys as $key) {
            if ($model->isDirty($key)) {
                $messages[] = $this->setMessage($key, $components);
            }
        }

        return $messages;
    }

    private function setMessage($key, $components)
    {
        $message       = $this->messages()[$key];
        $messageParams = preg_match_all('/\{\{ (.*?) \}\}/', $message, $params);

        foreach ($params[1] as $param) {
            $message = str_replace('{{ ' . $param . ' }}', $components[$param], $message);
        }

       return $message;
    }

    private function relations()
    {
        return $this->log['relations'] ? explodeString($this->log['relations']) : [];
    }

    private function components($model)
    {
        $components = [];
        $components_ = explodeString($this->log['components']);

        foreach ($components_ as $component) {
            $cutCmp = explodeString($component, ':');
            if (strpos($cutCmp[1] , '.' )) {
                $withRelation = explodeString($cutCmp[1], '.');
                $components[$cutCmp[0]] = $model->{$withRelation[0]}->{$withRelation[1]};
            } else {
                $components[$cutCmp[0]] = $model->{$cutCmp[1]};
            }
        }

        return $components;
    }

    private function messages()
    {
        $messages = [];
        $messages_ = explodeString($this->log['messages']);

        foreach ($messages_ as $message) {
            $cut = explodeString($message, ':');
            $messages[$cut[0]] = $cut[1];
        }

        return $messages;
    }

    private function dirtyKeys()
    {
        $keys     = [];
        $messages = explodeString($this->log['messages']);
        
        foreach ($messages as $message) {
            $cut    = explodeString($message, ':');
            $keys[] = $cut[0];
        }

        return $keys;
    }
}