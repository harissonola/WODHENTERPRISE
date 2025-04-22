<?php

namespace App\Controller;

use App\Entity\FormaLinks;
use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormaLinksRepository;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/formations', name: 'app_admin_formations_')]
class AdminFormationsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        FormationRepository $formationRepository
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $formations = $formationRepository->findAll();

        return $this->render('admin/formations/index.html.twig', [
            'controller_name' => 'AdminFormationsController',
            'formations' => $formations,
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Génération du slug
            if (!empty($form->get('name')->getData()) && $form->get('slug')->getData()) {
                $slugger = new AsciiSlugger();
                $formation->setSlug($slugger->slug($form->get('name')->getData())->lower());
            }

            // Gestion des images
            $image = $form->get('image')->getData();
            if ($image) {

                $newFilename = uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('formations_img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin_formations_add');
                }

                $formation->setImage($newFilename);

                $slugger = new AsciiSlugger();
                $qrCodePath = $this->generateQrCode($this->generateUrl('app_formations_details', [
                    'slug' => $slugger->slug($form->get('name')->getData())->lower(),
                ], UrlGeneratorInterface::ABSOLUTE_URL));

                // Ajoutez le chemin du QR Code au produit (si nécessaire)
                $formation->setQrCodePath($qrCodePath);

                $em->persist($formation);
                $em->flush();
            }

            $this->addFlash("success", "Formation ajouter avec succes.");
            return $this->redirectToRoute("app_admin_formations_index");
        }


        return $this->render('admin/formations/add.html.twig', [
            'controller_name' => 'AdminFormationsController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(
        $id,
        Request $request,
        EntityManagerInterface $em,
        FormationRepository $formationRepository
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $formation = $formationRepository->find($id);
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Génération du slug
            if (!empty($form->get('name')->getData()) && $form->get('slug')->getData()) {
                $slugger = new AsciiSlugger();
                $formation->setSlug($slugger->slug($form->get('name')->getData())->lower());
            }

            // Gestion des images
            $image = $form->get('image')->getData();
            if ($image) {
                $newFilename = uniqid() . '.' . $image->guessExtension();
                try {
                    $image->move(
                        $this->getParameter('formations_img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin_formations_add');
                }
                $formation->setImage($newFilename);
            }

            if($formation->getqrCodePath() == null){
                $qrCodePath = $this->generateQrCode($this->generateUrl('app_formations_details', [
                    'slug' => $formation->getSlug(),
                ], UrlGeneratorInterface::ABSOLUTE_URL));

                // Ajoutez le chemin du QR Code au produit (si nécessaire)
                $formation->setQrCodePath($qrCodePath);
            }

            if($formation->getqrCodePath() == ""){
                $qrCodePath = $this->generateQrCode($this->generateUrl('app_formations_details', [
                    'slug' => $formation->getSlug(),
                ], UrlGeneratorInterface::ABSOLUTE_URL));

                // Ajoutez le chemin du QR Code au produit (si nécessaire)
                $formation->setQrCodePath($qrCodePath);
            }

            // Gestion des liens (linkName et link) : récupérer les liens soumis via le repeater
            $linkNames = $form->get('linkName')->getData();
            $links = $form->get('link')->getData();

            // Si des liens ont été soumis
            if ($links && $linkNames) {
                $formaLink = new FormaLinks();
                $formaLink->setName($linkNames);  // Récupérer le nom du lien
                $formaLink->setLink($links);      // Récupérer l'URL du lien
                $formaLink->setFormation($formation); // Lier le lien à la formation actuelle

                // Persister explicitement l'entité FormaLink
                $em->persist($formaLink);
            }

            $em->persist($formation);
            $em->flush();

            $this->addFlash("success", "Formation modifiée avec succès.");
            return $this->redirectToRoute("app_admin_formations_index");
        }

        return $this->render('admin/formations/add.html.twig', [
            'controller_name' => 'AdminFormationsController',
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }



    #[Route('/delete/{id}', name: 'delete')]
    public function delete(int $id, FormationRepository $formationRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        // Récupérer le formation à partir de son ID
        $formation = $formationRepository->find($id);

        // Vérifier si le formation existe
        if (!$formation) {
            $this->addFlash('error', 'Formation non trouvé.');
            return $this->redirectToRoute('app_admin_product');
        }


        // Supprimer les images associées (si nécessaire)
        if ($formation->getImage()) {
            $imagePath = $this->getParameter('formations_img_directory') . '/' . $formation->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath); // Supprime le fichier image
            }
        }

        // Supprimer le formation
        $entityManager->remove($formation);
        $entityManager->flush();

        $this->addFlash('success', 'Formation supprimé avec succès.');
        return $this->redirectToRoute('app_admin_formations_index');
    }

    #[Route('/link/delete/{id}', name: 'links_delete')]
    public function imgDelete(
        int $id,
        FormaLinksRepository $formaLinksRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $link = $formaLinksRepository->find($id); // Utilisez find() et non findBy()

        if (!$link) {
            throw $this->createNotFoundException('Lien non trouvé.');
        }

        // Supprimer le lien
        $entityManager->remove($link);
        $entityManager->flush();

        // Ajoutez une redirection ou un retour approprié
        $this->addFlash('success', 'Lien supprimée avec succès.');
        return $this->redirectToRoute('app_admin_formations_edit', ['id' => $link->getFormation()->getId()]);
    }

    private function generateQrCode(string $data): string
    {
        // Initialiser le Filesystem de Symfony
        $filesystem = new Filesystem();

        // Définir le chemin du dossier et du fichier QR Code
        $qrCodeDir = $this->getParameter('kernel.project_dir') . '/public/formations/qr_codes/';
        $qrCodeFileName = 'qr_code_' . uniqid() . '.png';
        $qrCodePath = $qrCodeDir . $qrCodeFileName;

        // Vérifier et créer le dossier s'il n'existe pas
        if (!$filesystem->exists($qrCodeDir)) {
            $filesystem->mkdir($qrCodeDir, 0755);
        }

        // Générer le QR Code
        $qrCode = new QrCode($data);

        // Créer le Writer
        $writer = new PngWriter();

        // Écrire l'image du QR Code dans un fichier
        file_put_contents($qrCodePath, $writer->write($qrCode)->getString());

        // Retourner le chemin relatif du QR Code (pour les templates ou autres usages)
        return '/formations/qr_codes/' . $qrCodeFileName;
    }
}
