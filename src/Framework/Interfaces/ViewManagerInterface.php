<?php

namespace App\Framework\Interfaces;

use App\Framework\Model\BeContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


interface ViewManagerInterface {

    public function getView(BeContext $context, Request $request, $content): Response;
}
