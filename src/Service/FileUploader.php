<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, $fileName)
    {
        
        $file->move($this->getTargetDirectory(), $fileName);
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
