<?php

namespace App\Controller;


use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use App\Form\SearchContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Contact;
use App\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ContactType;

class ContactController extends AbstractController
{

    public function index(ContactRepository $repository, Request $request): Response
    {
        $contacts = $repository->findBy([], ['id' => 'DESC']);

        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $categorySearch);
        $form->handleRequest($request);


        $formSearch = $this->createForm(SearchContactType::class);
        $search = $formSearch->handleRequest($request);

        if($formSearch->isSubmitted() && $formSearch->isValid())
        {
            $contacts = $repository->search($search->get('mots')->getData());

        }


        if($form->isSubmitted() && $form->isValid())
        {
            $category = $categorySearch->getCategory();

            if($category)
            {
                $contacts = $repository
                    ->createQueryBuilder('c')
                    ->select(['c.id, c.fName, c.lName, c.phone, c.image, category.id, category.title'])
                    ->leftJoin('c.category','category')
                    ->where('category.id = :category')
                    ->setParameter('category', $category)
                    ->getQuery()
                    ->execute();
            }
        }

        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
            'formCategory' => $form->createView(),
            'contacts' => $contacts,
            'formSearch' => $formSearch->createView(),
        ]);
    }

    // à la place de $id => Contact $contact
    public function form(Contact $contact = null, Request $request)
    {

        if(!$contact)
        {
            $contact = new Contact();
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {


            $file = $form->get('image')->getData();
            $uploads_directory = $this->getParameter('uploads_directory');
            // On génère un nouveau nom de fichier
            $filename = md5(uniqid()).'.'.$file->guessExtension();

            // On copie le fichier dans le dossier uploads
            $file->move(
                $uploads_directory,
                $filename
            );

            // On stocke les données dans la bdd
            $contact->setImage($filename);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($contact);
            $manager->flush();

            return $this->redirectToRoute('contact_show', ['id'=>$contact->getId()]);
        }

        return $this->render('contact/form.html.twig', [
            'formContact' => $form->createView(),
            'editMode' => $contact->getId() !== null,
        ]);

    }

    //@ParamConverter route qui contient un argument $id qui se trouve dans la route show/{id}
    public function show(Contact $contact)
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact
        ]);
    }


    public function delete($id)
    {
        $message = "Impossible de supprimer le contact!";
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, $message);


        $manager = $this->getDoctrine()->getManager();
        $contact = $manager->getRepository(Contact::class)->find($id);

        $manager->remove($contact);
        $manager->flush();

        return $this->redirectToRoute('contact_index');

    }
}
