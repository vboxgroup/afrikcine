<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\DataCollector;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class RequestCollector extends AbstractDataCollector
{
    public function __construct(PleaseService $please)
    {
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->data = [
            'method' => $request->getMethod(),
            'acceptable_content_types' => $request->getAcceptableContentTypes()
        ];
    }

    public static function getTemplate(): ?string
    {
        return 'backoffice/data_collector/template.html.twig';
    }

    public function getMethod()
    {
        return $this->data['method'];
    }

    public function getAcceptableContentTypes()
    {
        return $this->data['acceptable_content_types'];
    }
    
    public function getName(): string
    {
        return 'DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\DataCollector\RequestCollector';
    }

    public function getUser()
    {
        return $_SESSION['_sf2_attributes']['User'] ?? null;
    }
}