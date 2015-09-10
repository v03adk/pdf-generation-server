<?php

namespace LinkORB\PdfGenerationServer\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LinkORB\PdfGenerationServer\Model\Template;

class TemplateController
{
    public function frontpageAction(Application $app, Request $request)
    {
        return new Response($app['twig']->render(
            'frontpage.html.twig'
        ));
    }

    public function indexAction(Application $app, Request $request)
    {
        $repo = $app->getTemplateRepository();
        $templates = $repo->getAll();

        $data = array();
        $data['templates'] = $templates;
        return new Response($app['twig']->render(
            'templates/index.html.twig',
            $data
        ));
    }

    public function viewAction(Application $app, Request $request, $templateId)
    {
        $repo = $app->getTemplateRepository();
        $template = $repo->getById($templateId);

        $data = array();
        $data['template'] = $template;
        return new Response($app['twig']->render(
            'templates/view.html.twig',
            $data
        ));
    }

    public function editAction(Application $app, Request $request, $templateId)
    {
        return $this->getTemplateEditForm($app, $request, $templateId);
    }

    public function addAction(Application $app, Request $request)
    {
        return $this->getTemplateEditForm($app, $request, null);
    }

    public function deleteAction(Application $app, Request $request, $templateId)
    {
        $repo = $app->getTemplateRepository();
        $template = $repo->getById($templateId);
        $repo->delete($template);

        return $app->redirect(
            $app['url_generator']->generate('templates_index')
        );
    }

    private function getTemplateEditForm(Application $app, Request $request, $templateId)
    {
        $error = $request->query->get('error');
        $repo = $app->getTemplateRepository();
        $add = false;

        $template = $repo->getById($templateId);

        if ($template === null) {
            $defaults = null;
            $add = true;
        } else {
            $defaults = [
                'name' => $template->getName(),
                'path' => $template->getPath(),
                'description' => $template->getDescription(),
            ];
        }

        $form = $app['form.factory']->createBuilder('form', $defaults)
            ->add('name', 'text')
            ->add('path', 'text')
            ->add('description', 'textarea', array('required' => false))
            ->getForm();

        // handle form submission
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            if ($add) {
                $template = new Template();
            }

            $template->setPath($data['path'])
                ->setName($data['name'])
                ->setDescription($data['description']);

            if ($add) {
                if (!$repo->add($template)) {
                    return $app->redirect(
                        $app['url_generator']->generate('templates_add', array('error' => 'Failed adding template'))
                    );
                }
            } else {
                $repo->update($template);
            }

            return $app->redirect($app['url_generator']->generate('templates_index'));
        }

        return new Response($app['twig']->render(
            'templates/edit.html.twig',
            [
                'form' => $form->createView(),
                'template' => $template,
                'error' => $error,
            ]
        ));
    }
}
