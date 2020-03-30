<?php

namespace App\Traits;

use App\Libraries\SingleImage;
use App\Libraries\SingleImage\Libraries\SingleImageSeeker;
use App\Libraries\SingleImage\Traits\SingleImageOptionsMacro;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Exception;

trait SingleImageTrait
{
    use SingleImageOptionsMacro;

    public $singleImage;
    public $singleImageRequest;
    protected $singleImageOptions;

    public static function bootSingleImageTrait()
    {
        static::saving(function(Model $model){
            $model->instantiateSingleImage();
            if ($model->singleImage->isDirty()) {
                $model->singleImage->delete();
                $model->singleImage->save();
                $model->attributes[$model->getImageColumn()] = $model->singleImage->uploader->getFilename();
                if ($model->hasAltColumn()) {
                    $model->attributes[$model->getAltColumn()] = $model->singleImage->getAlt();
                }
            }
        });
        
        static::deleting(function(Model $model){
            $model->instantiateSingleImage();
            $model->singleImage->delete();
        });
        
        static::retrieved(function(Model $model){
            $model->instantiateSingleImage();
        });
    }

    public function getRatio()
    {
        return $this->getAvailableDimensions()['default']['w'] / $this->getAvailableDimensions()['default']['h'];
    }

    public function deleteImage()
    {
        return $this->image()->delete();
    }

    public function image()
    {
        return $this->singleImage;
    }

    public function instantiateSingleImage()
    {
        $this->singleImage = new SingleImage($this);
    }

    public function setImageAttribute($value)
    {
        $this->singleImageRequest['image'] = $value;
    }

    public function setCropperAttribute($value)
    {
        $this->singleImageRequest['cropper'] = $value;
    }

    public function setImageNameAttribute($value)
    {
        $this->singleImageRequest['image_name'] = $value;
    }

    public function getImageAttribute()
    {
        return (new SingleImageSeeker($this->singleImageOptions()))->setFilename($this->attributes[$this->getImageColumn()] ?? null);
    }

    public function singleImageOptions()
    {
        if ($this->singleImageOptions) {
            return $this->singleImageOptions;
        }

        return $this->constructSingleImageOptions();
    }

    public function setSingleImageOptions(array $value)
    {
        $this->singleImageOptions = $value;
    }

    public function constructSingleImageOptions()
    {
        $singleImageTrait = $this->singleImageTrait ?? [];

        return $this->singleImageOptions = [
            'dir'                => $singleImageTrait['dir'] ?? 'storage/images/' . $this->getTable(), 
            'table'              => $this->getTable(),
            'dimensions'         => $singleImageTrait['dimensions'] ?? $this->convertDimensionToDimensions(),
            'altColumn'          => $singleImageTrait['altColumn'] ?? 'alt',
            'alt'                => $singleImageTrait['alt'] ?? false,
            'column'             => $singleImageTrait['column'] ?? 'image',
            'strict'             => $singleImageTrait['strict'] ?? false,
            'disablePlaceholder' => $singleImageTrait['disablePlaceholder'] ?? false,
            'defaultImage'       => $singleImageTrait['defaultImage'] ?? false,
        ];
    }

    public function convertDimensionToDimensions()
    {
        if (array_key_exists('dimension', $this->singleImageTrait)) {
            if (array_key_exists('w', $this->singleImageTrait['dimension']) && 
                array_key_exists('h', $this->singleImageTrait['dimension'])) {
                    // dd($this->singleImageTrait['dimension']);
                return [ 'default' => $this->singleImageTrait['dimension']];
            }
        }

        if (!array_key_exists('dimensions', $this->singleImageTrait)) {
            throw new Exception('Wrong singleImageTrait value in class ' . get_class());
        }
    }
}