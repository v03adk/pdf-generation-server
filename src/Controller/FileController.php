<?php

namespace LinkORB\PdfGenerationServer\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class FileController
{
    public function indexAction(Application $app, Request $request)
    {
        $repo = $app->getFileRepository();
        $files = $repo->getAll();

        $data = array();
        $data['files'] = $files;
        return new Response($app['twig']->render(
            'files/index.html.twig',
            $data
        ));
    }

    public function downloadAction(Application $app, Request $request, $fileId)
    {
        $repo = $app->getFileRepository();
        $file = $repo->getById($fileId);


        $response = new BinaryFileResponse($file->getPath());
        $response->prepare(Request::createFromGlobals());
        $response->send();
    }
}