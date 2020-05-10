<?php

namespace App\BeBundle\Framework\Interfaces;
use App\BeBundle\Framework\Model\BeContext;
use Symfony\Component\HttpFoundation\Request;


interface MiddlewareInterface {

    public function getAll(BeContext $context, Request $request);
    public function post(BeContext $context, Request $request);
}