<?php

//permet d'effectuer un upload

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UploadService
{
    public function upload(UploadedFile $file, string $oldFile = null): string
    {
        //recupere le nom original du fichier envoyé
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        //"slugify" le nom du fichier
        $slugger = new AsciiSlugger();
        $safeFilename = $slugger->slug($originalFilename);
        $uniqid = uniqid();
        
        //nouveau nom du fichier : nom_original_125858.png
        $newFilename = "$safeFilename-$uniqid.{$file->guessExtension()}";
        
        //upload
        $file->move('avatars', $newFilename);

        //instancie le composant  symfony filesysteme
        $filesystem = new Filesystem;

        //supprime l'ancienne image
        if($oldFile !== null && $filesystem->exists("$oldFile") && $oldFile !== 'imgs/user_default.jpg'){
            //alors on supprime celui-ci
            $filesystem->remove("$oldFile");
        }

        //retourne le nouveau nom du fichier
        return $newFilename;
    }
}