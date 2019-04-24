<?php

class ImageUploader
{
    public function __construct()
    {

    }

    public function uploadImages($files = array())
    {
        $subjectId = $_POST['subjectid'];
        for($i=0; $i < count($files['filename']['tmp_name']); $i++ )
        {
            $file = $files['filename']['tmp_name'][$i];
            if ($handle = fopen($file, "r")) {

                $image = new SubjectImage();
                $image->SubjectId = (int)$subjectId;
                $image->Filename = str_replace(' ', '', $files['filename']['name'][$i]);
                $image->Location = $image::IMAGE_DIR;
                $image->save($i);
            }
        }
    }
}