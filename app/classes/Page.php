<?php

namespace App;

class Twig
{
    private \Twig\Environment $twig;

    function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('../templates');
        $this->twig = new \Twig\Environment($loader, [
            'cache' => '../var/cache/compilation_cache',
        ]);
    }

    function render(string $name, array $data) :string
    {
        return $this->twig->render($name, $data);
    }
}