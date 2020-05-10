<?php

namespace App\BeBundle\Framework\View\Manager;


use App\BeBundle\Framework\Interfaces\ViewManagerInterface;
use App\BeBundle\Framework\Model\BeContext;
use App\BeBundle\Framework\View\Model\QueryFieldsParser;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class DefaultViewManager implements ViewManagerInterface {



    public function getView(BeContext $context, ?string $serializeModel,int $statusCode, $content): Response {
        return $this->createView($content,$statusCode,$context,$serializeModel);
    }


    public function createView($content, int $responseStatus, BeContext $context = null, $view = null): Response {
        (!$context) ? $context = (new BeContext())->setGroups(['public']) : null;
        $serializedContent = $this->serializeResponse($content, $context, $view);
        return new Response($serializedContent, $responseStatus, ['content-type' => 'application/json']);

    }

    public function serializeResponse($content, BeContext $context, $queryFields): string {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\\TH:i:s.v\\Z']),
            new ObjectNormalizer($classMetadataFactory),
        ];
        $serializer = new Serializer($normalizers, $encoders);
        if ($queryFields) {
            $view = (new QueryFieldsParser())->parse($queryFields);
            $serializedContent = $serializer->serialize($content,
                'json',
                [
                    ObjectNormalizer::ENABLE_MAX_DEPTH => true,
                    ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) { return $object->getId(); },
                    'attributes' => $view,
                    'groups' => ($context->hasAttribute('groups') ? $context->getAttribute('groups') : []),
                ]);
        } else {
            $serializedContent = $serializer->serialize($content,
                'json',
                [
                    ObjectNormalizer::ENABLE_MAX_DEPTH => true,
                    'groups' => ($context->hasAttribute('groups') ? $context->getAttribute('groups') : []),
                    ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) { return $object->getId(); },
                ]);
        }

        return $serializedContent;
    }

}