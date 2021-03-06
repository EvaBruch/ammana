<?php

/*!
 * ammana.es - job protocols generator
 * https://github.com/NoLegalTech/ammana
 * Copyright (C) 2018 Zeres Abogados y Consultores Laborales SLP <zeres@zeres.es>
 * https://github.com/NoLegalTech/ammana/blob/master/LICENSE
 */

namespace AppBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use AppBundle\Entity\NewsletterSubscriber;

use AppBundle\Service\PermissionsService;

class ProfileController extends Controller {

    public function indexAction(Request $request, SessionInterface $session, PermissionsService $permissions)
    {
        if (!$permissions->currentRolesInclude("adviser") && !$permissions->currentRolesInclude("customer") && !$permissions->currentRolesInclude("admin")) {
            return $this->redirectToRoute('error', array(
                'message' => $this->getI18n()['errors']['restricted_access']['user']
            ));
        }

        $user = $permissions->getCurrentUser($session);

        $editForm = $this->createForm('AppBundle\Form\UserType', $user, array(
            'i18n' => $this->getI18n()
        ));
        $editForm->add('previous_logo', HiddenType::class, array(
            'data' => $user->getLogo(),
            'mapped' => false
        ));
        $editForm->add('delete_logo', CheckboxType::class, array(
            'label' => $this->getI18n()['forms']['profile_form']['delete_logo'],
            'required' => false,
            'mapped' => false
        ));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $previous_logo = $editForm->get('previous_logo')->getData();
            $delete_logo = $editForm->get('delete_logo')->getData();

            if ($delete_logo) {
                $user->setLogo(null);
            } else {
                $file = $user->getLogo();
                if ($file != null) {
                    $fileName = md5(uniqid()).'.'.$file->guessExtension();
                    $file->move($this->get('kernel')->getRootDir(). '/../web/uploads', $fileName);
                    $user->setLogo($fileName);
                } else {
                    $user->setLogo($previous_logo);
                }
            }

            $this->getDoctrine()->getManager()->flush();

            $continue = $request->get('continue');
            if ($continue != null) {
                return $this->redirectToRoute('protocol_buy', array('id' => $continue));
            }

            return $this->redirectToRoute('profile_homepage');
        }

        return $this->render('user/profile.html.twig', array(
            'title' => $this->getI18n()['profile_page']['title'],
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'continue' => $request->query->get('continue'),
            'google_analytics' => $this->getAnalyticsCode(),
            'newsletter_form' => $this->getNewsletterForm($request)->createView()
        ));
    }

    private function getI18n() {
        return $this->container->get('twig')->getGlobals()['i18n']['es'];
    }

    private function getAnalyticsCode() {
        return $this->container->hasParameter('google_analytics')
            ? $this->container->getParameter('google_analytics')
            : null;
    }

    private function getNewsletterForm(Request $request)
    {
        $subscriber = new NewsletterSubscriber();

        $form = $this->createForm('AppBundle\Form\NewsletterType', $subscriber, array(
            'i18n' => $this->getI18n()
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subscriber);

            try {
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
            }

            unset($subscriber);
            unset($form);
            $subscriber = new NewsletterSubscriber();
            $form = $this->createForm('AppBundle\Form\NewsletterType', $subscriber, array(
                'i18n' => $this->getI18n()
            ));
        }


        return $form;
    }

}
