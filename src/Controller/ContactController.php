<?php

namespace App\Controller;

use App\DTO\contactDTO;
use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        MailerInterface $mailer
    ): Response {
        $data = new contactDTO();

        $form = $this->createForm(ContactType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //envoyer l'email
            $mail = (new TemplatedEmail())
                ->to("info@wodh.enterprise.com")
                ->from($data->email)
                ->subject($data->subject)
                ->htmlTemplate("mail/contact.html.twig")
                ->context(['data' => $data]);

            try {
                $mailer->send($mail);
                $this->addFlash('success', "Message envoyer avec succes");
                return $this->redirectToRoute("app_contact");
            } catch (TransportExceptionInterface $e) {
                //throw $th;

                $this->addFlash('danger', "Une erreur est survenue lors de l'envoie de votre message : " . $e->getMessage());
                return $this->redirectToRoute("app_contact");
            }
        }


        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
            'form' => $form,
        ]);
    }
}
