<?php

namespace App\Framework\Controller;

use App\Data\Entity\User;
use App\Framework\Form\UserType;
use App\Framework\Service\BeControllerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController {

    private BeControllerService $cs;

    public function __construct(BeControllerService $controllerService) {
        $this->cs = $controllerService;
        //$this->cs->setManagedEntityName(User::class);
        $this->cs->setManagedEntityFormName(UserType::class);
    }

    /**
     * @Route("/users", methods={"GET"})
     * @param Request $request
     *
     * @return Response
     */
    public function getUsers(Request $request): Response {
        return $this->cs->getAllEndpoint($request);
    }

    /**
     * @Route("/users", methods={"POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function postUsers(Request $request): Response {
        return $this->cs->postEndpoint($request);
    }


    //TODO: test coger con el framework todas las routes de los endpoint y que no den 500
    /**
     * @Route("/default/{modelName}", methods={"GET"})
     * @param Request $request
     *
     * @param string  $modelName
     *
     * @return Response
     *
     * public function getAllEndpoint(Request $request, string $modelName): Response {
     * //idea del controller genérico
     * $name = 'App\\Data\\Entity\\'.ucfirst($modelName);
     * $entityManager = $this->getDoctrine()->getManager();
     * $users = $entityManager->getRepository($name)->findAll();
     *
     * return new Response(json_encode($users, true), Response::HTTP_OK, ['content-type' => 'application/json']);
     * }
     */
}


