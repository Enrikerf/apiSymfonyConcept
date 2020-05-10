<?php

namespace App\BeBundle\Framework\Interfaces;


use App\BeBundle\Framework\Model\BeContext;
use Symfony\Component\HttpFoundation\Response;


interface ViewManagerInterface {

    public function getView(BeContext $context, ?string $serializedModel,int $statusCode, $content): Response;
}
