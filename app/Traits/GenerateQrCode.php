<?php

namespace App\Traits;

use QrCode;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;

trait GenerateQrCode {

    private $model;

    public static function bootGenerateQrCode()
    {
        $self = new self();

        static::created(function($model) use ($self) {
            $self->setModel($model);
            $self->qrCodeSave();
        });

        static::updated(function($model) use ($self) {
            $self->setModel($model);

            if (!file_exists(public_path($self->fullpathFile())) || $model->isDirty($self->getQrField())) {
                $self->qrCodeSave();
            }
        });
    }

    public function getQrQodeAttribute()
    {
        $fullpath = $this->fullpathFile();

        if (file_exists(public_path($fullpath))) {
            return asset($fullpath);
        }
    }

    public function qrFileName()
    {
        return $this->getUniqueCode() . '.png';
    }

    public function fullpathFile()
    {
        $filename = $this->qrFileName();

        return $this->getDirQR() . '/' . $filename;
    }

    public function getTextCode()
    {
        return route('redirect.download', $this->getUniqueCode());
    }

    public function getUniqueCode()
    {
        $model = $this->model ?? $this;

        return str_slug($model->{static::getQrField()});
    }

    public function qrCodeSave()
    {
        $this->createDir($this->getDirQR());
        
        $fullPath = public_path($this->fullpathFile());
        $code     = $this->getTextCode();

        $this->deleteIfExist();

        QrCode::format('png')
                    ->size(300)
                    ->margin(1)
                    ->color(0, 98, 128)
                    ->errorCorrection('H')
                    ->generate($code, $fullPath);
    }

    public function deleteIfExist()
    {
        $file = public_path($this->fullpathFile());
        if (file_exists($file)) {
            unlink($file) or die("Couldn't delete file");
        }
    }

    public function getDirQR()
    {
        return 'storage/'. $this->getUniqueCode() .'/' . $this->getTable() . '/images/qrcode';
    }

    public function createDir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    public static function getQrField()
    {
        return isset(static::$qrField) ? static::$qrField : 'id';
    }

    public function setModel($model)
    {
        $this->model = $model;
    }
}