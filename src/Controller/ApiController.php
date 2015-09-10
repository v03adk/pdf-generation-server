<?php

namespace LinkORB\PdfGenerationServer\Controller;

use LinkORB\PdfGenerationServer\Model\File;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\PdfGenerationServer\Model\Template;
use Rhumsaa\Uuid\Uuid;
use Knp\Snappy\Pdf;

class ApiController
{
    public function rootAction(Application $app, Request $request)
    {
        $data = $this->prepareInput();

        if ($data === null)
            return new JsonResponse(['error' => 'no json data found'], 400);

        $templateName = isset($data['template']) ? $data['template'] : null;
        $templateData = isset($data['data']) ? $data['data'] : null;

        if (!$templateName || !$templateData)
            return new JsonResponse(['error' => 'template and data must be set'], 400);


        $repo = $app->getTemplateRepository();
        $template = $repo->getByName($templateName);

        if (!$template)
            return new JsonResponse(['error' => "template $templateName not found"], 404);

        $twig = new \Twig_Environment(new \Twig_Loader_String());
        $html = $twig->render($template->getTemplate(), $templateData);

        $file = new File();
        $file->setId(Uuid::uuid4()->toString());
        $file->setCreatedAt(date('Y-m-d H:i:s'));
        $file->setPath($this->getFilePath($file));

        $snappy = new Pdf();
        if (substr(php_uname(), 0, 7) == "Windows") {
            $snappy->setBinary('vendor\bin\wkhtmltopdf.exe.bat');
        }
        else {
            $snappy->setBinary('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');
        }

        $snappy->generateFromHtml($html, $file->getPath());

        $repo = $app->getFileRepository();
        $repo->add($file);

        return new JsonResponse(['id' => $file->getId()], 201);
    }

    public function downloadFileAction(Application $app, Request $request, $fileId)
    {
        if (!$fileId)
            return new JsonResponse(['error' => 'file id must be set'], 400);


        $repo = $app->getFileRepository();
        $file = $repo->getById($fileId);

        if (!$file)
            return new JsonResponse(['error' => "file with id $fileId not found"], 404);

        return new JsonResponse(['content' => base64_encode(file_get_contents($file->getPath()))], 200);
    }

    private function prepareInput()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    private function getFilePath(File $file)
    {
        $storagePath = __DIR__.'/../../files/'.date('Y/m/d', strtotime($file->getCreatedAt()));
        if(!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }
        return $storagePath.'/'.$file->getId();
    }
}