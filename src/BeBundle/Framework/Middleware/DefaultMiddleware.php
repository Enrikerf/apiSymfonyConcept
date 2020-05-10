<?php

namespace App\BeBundle\Framework\Middleware;

use App\BeBundle\Framework\Interfaces\MiddlewareInterface;
use App\BeBundle\Framework\Model\BeContext;
use Symfony\Component\HttpFoundation\Request;


class DefaultMiddleware implements MiddlewareInterface {

    public function getAll(BeContext $context, Request $request) {
        $context->setGroups(['public']);
        // TODO: Implement getAll() method.
    }

    public function post(BeContext $context, Request $request) {
        $context->setGroups(['public']);
    }
}