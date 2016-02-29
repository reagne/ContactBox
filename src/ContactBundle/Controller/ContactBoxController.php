<?php

namespace ContactBundle\Controller;

use ContactBundle\Entity\Address;
use ContactBundle\Entity\Email;
use ContactBundle\Entity\Groups;
use ContactBundle\Entity\Person;
use ContactBundle\Entity\Phone;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ContactBoxController extends Controller
{
// FORMULARZE:
    private function personForm($person, $action){
        $form = $this->createFormBuilder($person);
        $form->add('firstname', 'text', ['label' => 'Podaj imię: ']);
        $form->add('lastname', 'text', ['label' => 'Podaj nazwisko: ']);
        $form->add('description', 'textarea', ['label' => 'Podaj opis: ']);
        $form->add('groups', 'entity', ['label' => 'Grupy: ', 'class' => 'ContactBundle\Entity\Groups', 'choice_label' => 'name', 'expanded' => 'true', 'multiple' =>'true']);
        $form->add('save', 'submit', ['label' => 'Zapisz']);
        $form->setAction($action);
        $personForm = $form->getForm();

        return $personForm;
    }
    private function addressForm($address, $action){
        $form = $this->createFormBuilder($address);
        $form->add('city', 'text', ['label' => 'Podaj miasto: ']);
        $form->add('street', 'text', ['label' => 'Podaj ulicę: ']);
        $form->add('house_no', 'integer', ['label' => 'Podaj nr domu: ']);
        $form->add('flat_no', 'integer', ['label' => 'Podaj nr mieszkania: ', 'required' => false]);
        $form->add('save', 'submit', ['label' => 'Zapisz']);
        $form->setAction($action);
        $addressForm = $form->getForm();

        return $addressForm;
    }
    private function emailForm($mail, $action){
        $form = $this->createFormBuilder($mail);
        $form->add('mail', 'text', ['label' => 'Podaj e-mail: ', 'required' => false]);
        $form->add('type', 'text', ['label' => 'Podaj typ: ']);
        $form->add('save', 'submit', ['label' => 'Zapisz']);
        $form->setAction($action);
        $emailForm = $form->getForm();

        return $emailForm;
    }
    private function phoneForm($phone, $action){
        $form = $this->createFormBuilder($phone);
        $form->add('number', 'text', ['label' => 'Podaj nr telefonu: ']);
        $form->add('type', 'text', ['label' => 'Podaj typ: ']);
        $form->add('save', 'submit', ['label' => 'Zapisz']);
        $form->setAction($action);
        $phoneForm = $form->getForm();

        return $phoneForm;
    }
    private function groupForm($group){
        $form = $this->createFormBuilder($group);
        $form->add('name', 'text', ['label' => 'Podaj nową nazwę grupy: ']);
        $form->add('save', 'submit', ['label' => 'Zapisz']);
        $form->setAction($this->generateUrl('createGroup'));
        $groupForm = $form->getForm();

        return $groupForm;
    }

//PODSTAWOWE DZIAŁANIA

//Wyświetlanie wszystkich osób
    /**
     * @Route("/allContacts", name="all")
     * @Template("ContactBundle:Contacts:getAllContacts.html.twig")
     */
    public function showAllContacts(){
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $persons = $repo->findAllAlphabetically();

        return ['persons' => $persons];
    }

//Wyświetlanie jednej osoby
    /**
     * @Route("/showContact/{id}", name="showPerson")
     * @Template("ContactBundle:Contacts:showPerson.html.twig")
     */
    public function showPersonAction($id){
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $person = $repo->find($id);

        return['person' => $person];
    }


//DODAWANIE NOWYCH DANYCH

// OSOBA
    /**
     * @Route("/newPerson", name="addPerson")
     * @Template("ContactBundle:Contacts:newPerson.html.twig")
     */
    public function newPersonAction(){
        $person = new Person();
        $personForm = $this->personForm($person, $this->generateUrl('createPerson'));

        return['person' => $personForm->createView()];
    }
    /**
     * @Route("/addContact", name="createPerson")
     */
    public function createPersonAction(Request $req)
    {
        $person = new Person();

        $personForm = $this->personForm($person, $this->generateUrl('createPerson'));
        $personForm->handleRequest($req);

        if ($personForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
        }
        $newId = $person->getId();

        return $this->redirectToRoute('showPerson', ['id' => $newId]);
        //return new Response("zapisano");
    }

// ADRES
    /**
     * @Route("/newAddress/{id}", name="addAddress")
     * @Template("ContactBundle:Address:newAddress.html.twig")
     */
    public function newAddressAction($id){
        $address = new Address();
        $addressForm = $this->addressForm($address, $this->generateUrl('createAddress', ['id' => $id]));

        return['address' => $addressForm->createView(), 'id' => $id];
    }
    /**
     * @Route("/addAddress/{id}", name="createAddress")
     */
    public function createAddressAction(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $person = $repo->find($id);

        $address = new Address();

        $addressForm = $this->addressForm($address, $this->generateUrl('createAddress', ['id' => $id]));
        $addressForm->handleRequest($req);

        if ($addressForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $address->setPerson($person);
            $person->addAddress($address);
            $em->persist($address);
            $em->flush();
        }

        return $this->redirectToRoute('addMail', ['id' => $id]);
    }

// MAIL
    /**
     * @Route("/newMail/{id}", name="addMail")
     * @Template("ContactBundle:Email:newMail.html.twig")
     */
    public function newMailAction($id){
        $mail = new Email();
        $mailForm = $this->emailForm($mail, $this->generateUrl('createMail', ['id' => $id]));

        return['mail' => $mailForm->createView(), 'id' => $id];
    }
    /**
     * @Route("/addMail/{id}", name="createMail")
     */
    public function createMailAction(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $person = $repo->find($id);

        $mail = new Email();

        $mailForm = $this->emailForm($mail, $this->generateUrl('createMail', ['id' => $id]));
        $mailForm->handleRequest($req);

//        $validator = $this->get('validator');
//        $errors = $validator->validate($mail);
//
//        if (count($errors) > 0){
//            return $this->render('ContactBundle:Contacts:addError.html.twig', ['errors' => $errors]);
//        }
// Zamiast tego co jest wyżej można dać jedynie dodatkowy warunek

        if ($mailForm->isSubmitted() && $mailForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $mail->setPerson($person);
            $person->addMail($mail);
            $em->persist($mail);
            $em->flush();

            return $this->redirectToRoute('addPhone', ['id' => $id]);
        } else {
            return $this->render('ContactBundle:Contacts:newMail.html.twig', ['mail' => $mailForm->createView(), 'id' => $id]);
        }


    }

// TELEFON
    /**
     * @Route("/newPhone/{id}", name="addPhone")
     * @Template("ContactBundle:Phone:newPhone.html.twig")
     */
    public function newPhoneAction($id){
        $phone = new Phone();
        $phoneForm = $this->phoneForm($phone, $this->generateUrl('createPhone', ['id' => $id]));

        return['phone' => $phoneForm->createView(), 'id' => $id];
    }
    /**
     * @Route("/addPhone/{id}", name="createPhone")
     */
    public function createPhoneAction(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $person = $repo->find($id);

        $phone = new Phone();

        $phoneForm = $this->phoneForm($phone, $this->generateUrl('createPhone', ['id' => $id]));
        $phoneForm->handleRequest($req);

        if ($phoneForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $phone->setPerson($person);
            $person->addPhone($phone);
            $em->persist($phone);
            $em->flush();
        }

        return new Response('Dodano wszystkie dane');
    }

// EDYCJA DANYCH

// OSOBY

    /**
     * @Route("/editPerson/{id}", name="editPerson")
     * @Template("ContactBundle:Contacts:editPerson.html.twig")
     */
    public function editPersonAction($id){
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $person = $repo->find($id);
        $personForm = $this->personForm($person, $this->generateUrl('editPerson2', ['id' => $id]));

        return['person' => $personForm->createView()];
    }
    /**
     * @Route("/editPerson2/{id}", name="editPerson2")
     */
    public function editPerson2Action(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $person = $repo->find($id);

        $personForm = $this->personForm($person, $this->generateUrl('editPerson2', ['id' => $id]));
        $personForm->handleRequest($req);

        if ($personForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $this->redirectToRoute('showPerson', ['id' => $id]);
    }

// ADRESU
    /**
     * @Route("/editAddress/{id}", name="editAddress")
     * @Template("ContactBundle:Address:editAddress.html.twig")
     */
    public function editAddressAction($id){
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Address');
        $address = $repo->find($id);
        $person_id = $address->getPerson()->getId();

        $addressForm = $this->addressForm($address, $this->generateUrl('editAddress2', ['id' => $id]));

        return['address' => $addressForm->createView(), 'person_id' => $person_id];
    }
    /**
     * @Route("/editAddress2/{id}", name="editAddress2")
     */
    public function editAddress2Action(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Address');
        $address = $repo->find($id);
        $person_id = $address->getPerson()->getId();

        $addressForm = $this->addressForm($address, $this->generateUrl('editAddress2', ['id' => $id]));
        $addressForm->handleRequest($req);

        if ($addressForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $this->redirectToRoute('showPerson', ['id' => $person_id]);
    }

// EMAIL
    /**
     * @Route("/editMail/{id}", name="editMail")
     * @Template("ContactBundle:Email:editMail.html.twig")
     */
    public function editMailAction($id){
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Email');
        $mail = $repo->find($id);
        $person_id = $mail->getPerson()->getId();

        $mailForm = $this->emailForm($mail, $this->generateUrl('editMail2', ['id' => $id]));

        return['mail' => $mailForm->createView(), 'person_id' => $person_id];
    }
    /**
     * @Route("/editMail2/{id}", name="editMail2")
     */
    public function editMail2Action(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Email');
        $mail = $repo->find($id);
        $person_id = $mail->getPerson()->getId();

        $mailForm = $this->emailForm($mail, $this->generateUrl('editMail2', ['id' => $id]));
        $mailForm->handleRequest($req);

        if ($mailForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $this->redirectToRoute('showPerson', ['id' => $person_id]);
    }

// TELEFONU
    /**
     * @Route("/editPhone/{id}", name="editPhone")
     * @Template("ContactBundle:Phone:editPhone.html.twig")
     */
    public function editPhoneAction($id){
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Phone');
        $phone = $repo->find($id);
        $person_id = $phone->getPerson()->getId();

        $phoneForm = $this->phoneForm($phone, $this->generateUrl('editPhone2', ['id' => $id]));

        return['phone' => $phoneForm->createView(), 'person_id' => $person_id];
    }
    /**
     * @Route("/editPhone2/{id}", name="editPhone2")
     */
    public function editPhone2Action(Request $req, $id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Phone');
        $phone = $repo->find($id);
        $person_id = $phone->getPerson()->getId();

        $phoneForm = $this->phoneForm($phone, $this->generateUrl('editPhone2', ['id' => $id]));
        $phoneForm->handleRequest($req);

        if ($phoneForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return $this->redirectToRoute('showPerson', ['id' => $person_id]);
    }

// USUWANIE DANYCH

// OSOBY

    /**
     * @Route("/removeContact/{id}", name="removePerson")
     */
    public function removePersonAction($id){
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Person');
        $person = $repo->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($person);
        $em->flush();

        return $this->redirectToRoute('all');
    }


// ADRESU

    /**
     * @Route("/removeAddress/{id}", name="removeAddress")
     */
    public function removeAddressAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Address');
        $address = $repo->find($id);
        $person_id = $address->getPerson()->getId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($address);
        $em->flush();

        return $this->redirectToRoute('showPerson', ['id' => $person_id]);
    }

// E-MAILA

    /**
     * @Route("/removeMail/{id}", name="removeMail")
     */
    public function removeMailAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Email');
        $mail = $repo->find($id);
        $person_id = $mail->getPerson()->getId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($mail);
        $em->flush();

        return $this->redirectToRoute('showPerson', ['id' => $person_id]);
    }

// TELEFONU

    /**
     * @Route("/removePhone/{id}", name="removePhone")
     */
    public function removePhoneAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('ContactBundle:Phone');
        $phone = $repo->find($id);
        $person_id = $phone->getPerson()->getId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($phone);
        $em->flush();

        return $this->redirectToRoute('showPerson', ['id' => $person_id]);
    }

// GRUPY

// Dodawanie nowych grup

    /**
     * @Route("/newGroup", name="newGroup")
     * @Template("ContactBundle:Groups:newGroup.html.twig")
     */
    public function newGroupAction()
    {
        $group = new Groups();
        $groupForm = $this->groupForm($group);

        return['group' => $groupForm->createView()];
    }
    /**
     * @Route("/createGroup", name="createGroup")
     */
    public function createGroupAction(Request $req)
    {
        $group = new Groups();

        $groupForm = $this->groupForm($group);
        $groupForm ->handleRequest($req);

        if ($groupForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();
        }

        return $this->redirectToRoute('newGroup');
    }
}
