<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\Model;

trait ImageHandlerTrait
{
    public static function bootImageHandlerTrait()
    {
        $self = new self();
        static::creating(function(Model $model) use ($self) {
            
            $image = $self->uploadImage($model->image);
            $model->{$self->getColumn()} = $image;
            
            if ($self->getColumn() != 'image') {
                unset($model['image']);
            }
        });

        static::updating(function(Model $model) use ($self) {
            if (is_file($model->image)) {
                $self->deleteImage($model->getOriginal()[$self->getColumn()]);
                $image = $self->uploadImage($model->image);
                $model->{$self->getColumn()} = $image;
            }

            if ($self->getColumn() != 'image') {
                unset($model['image']);
            }
        });

        static::deleted(function(Model $model) use ($self) {
            $self->deleteImage($model->getOriginal()[$self->getColumn()]);
        });
    }

    public function getImageAttribute($image)
    {
        if ($image && (is_file($image) && (stripos($image, 'assets/images') === false))) return $image;

        if ($this->defaultImage() && (stripos($image, 'assets/images') !== false )) {
            return asset($image);
        }

        if ($this->getColumn() == 'image') {
            return asset($this->getPath() . '/' . $image);
        }
        
        if (filter_var($this->{$this->getColumn()}, FILTER_VALIDATE_URL)) {
            return $this->{$this->getColumn()};
        }

        
        if ($this->{$this->getColumn()} == $this->defaultImage()) {
            return asset($this->{$this->getColumn()});
        }

        
        return asset($this->getPath() . '/' . $this->{$this->getColumn()});
    }

    public function deleteImage($image)
    {
        if ($image != $this->defaultImage()) {

            $image = public_path($this->getPath() . '/' . $image);
            if (file_exists($image)) {
                unlink($image);
            }
        }
    }

    public function uploadImage($image)
    {
        $name = $this->defaultImage();

        if (is_file($image)) {
            $name = $this->setName($image);
            $path = public_path($this->getPath());

            $image->move($path, $name);
        }

        return $name;
    }

    public function setName($image)
    {
        return generate_random(5) . time() . '.' . $image->getClientOriginalExtension();
    }

    public function getPath()
    {
        return 'storage/'. request()->route('business')->slug .'/' . $this->getTable() . '/images/' . $this->defaultFolder();
    }

    public function createDir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    public function defaultImage()
    {
        return $this->imageSetting['defaultImage'] ?? null;
    }

    public function defaultFolder()
    {
        return $this->imageSetting['folder'] ?? 'default';
    }

    public function getColumn()
    {
        return $this->imageSetting['column'] ?? 'image';
    }
}