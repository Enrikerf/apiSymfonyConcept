<?php

namespace App\Framework\Controller;

use App\BeBundle\Framework\Service\BeControllerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController {

    private BeControllerService $cs;

    public function __construct(BeControllerService $controllerService) {
        $this->cs = $controllerService;
    }

    /**
     * @Route("/default/{modelName}", methods={"GET"})
     */
    public function getAllDefault(string $modelName, Request $request): Response {
        $middlewareName = 'App\\Framework\\Middleware\\'.ucfirst($modelName).'Middleware';
        if (class_exists($middlewareName)) {
            $middleware = new $middlewareName();
            $this->cs->setMiddleware($middleware);
        }
        $this->cs->setManagedEntityName($modelName);

        return $this->cs->getAllEndpoint($request);
    }

    /**
     * @Route("/default/{modelName}", methods={"POST"})
     */
    public function postDefault(Request $request): Response {
        //return $this->cs->postEndpoint($request);
    }
}


