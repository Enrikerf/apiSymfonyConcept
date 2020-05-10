<?php

namespace App\Framework\Middleware;

use App\Framework\Interfaces\MiddlewareInterface;
use App\Framework\Model\BeContext;
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