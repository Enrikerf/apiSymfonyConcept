<?php

namespace App\Framework\Interfaces;
use App\Framework\Model\BeContext;
use Symfony\Component\HttpFoundation\Request;


interface MiddlewareInterface {

    public function getAll(BeContext $context, Request $request);
    public function post(BeContext $context, Request $request);
}