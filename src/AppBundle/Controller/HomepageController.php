<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Website;
use AppBundle\Service\DockerCommunicator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class HomepageController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(EntityManagerInterface $em, Request $request)
    {
        /** @var FormInterface $form */
        $form = $this->createFormBuilder()
          ->add('site', TextType::class)
          ->add('save', SubmitType::class, array(
              'label' => 'Save and restart',
              'attr' => array('class' => 'btn btn-large green waves-effect waves-light')
          ))
          ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $values = $form->getData();
            $url = $values['site'];
            if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
                die('Not a valid URL');
            }

            $container = $this->recreateBrowserSyncContainer($url);
            sleep(4);
            $this->reloadBrowserSync($container);
        }

        $websites = $em->getRepository('AppBundle:Website')
            ->findAll();

        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
            'websites' => $websites
        ]);
    }

    /**
     * @param $url
     * @return mixed|string
     */
    protected function recreateBrowserSyncContainer($url)
    {
        $docker = $this->get(DockerCommunicator::class);
        $containers = $docker->listContainers();
        foreach ($containers as $container) {
            foreach ($container->Names as $name) {
                if ($name === '/browser-sync') {
                    if ($container->State === 'running') {
                        $docker->killContainer($container);
                    }
                    $docker->removeContainer($container);
                }
            }
        }

        $container = $docker->createContainer('browser-sync', [
            'Image' => 'ustwo/browser-sync',
            'Cmd' => [
                'start',
                '--proxy',
                $url,
                '--no-open'
            ],
            'ExposedPorts' => [
                '3000/tcp' => new \stdClass(),
                '3001/tcp' => new \stdClass()
            ],
            'HostConfig' => [
                'PortBindings' => [
                    '3000/tcp' => [['HostPort' => '3000', 'HostIp' => '0.0.0.0']],
                    '3001/tcp' => [['HostPort' => '3001', 'HostIp' => '0.0.0.0']]
                ]

            ]
        ]);

        $docker->startContainer($container);
        return $container;
    }

    /**
     * @param $container
     */
    protected function reloadBrowserSync($container)
    {
        $docker = $this->get(DockerCommunicator::class);
        $commandId = $docker->createExec($container, [
            'Cmd' => [
                'browser-sync',
                'reload'
            ]
        ]);

        $docker->startExec($commandId);
    }
}
