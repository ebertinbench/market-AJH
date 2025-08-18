<?php

namespace App\Services;

class Wallpaper
{
    
    /**
     * Permet d'obtenir un nom de fond d'écran aléatoire. 
     * Cette fonction est nécessaire dans toutes les routes, sous la forme de la variable 'wallpaper'.
     */
    public function getRandomWallpaperName(): ?string
    {
        $directory = __DIR__ . '/../../assets/images/wallpapers';
        if (!is_dir($directory)) {
            return null;
        }

        $files = array_values(array_filter(
            scandir($directory),
            function ($file) use ($directory) {
                return is_file($directory . '/' . $file) &&
                    preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $file);
            }
        ));

        if (empty($files)) {
            return null;
        }

        return $files[array_rand($files)];
    }
}