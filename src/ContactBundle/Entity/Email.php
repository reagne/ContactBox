<?php

namespace ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use  Symfony\Component\Validator\Constraints as Assert;

/**
 * Email
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="ContactBundle\Entity\EmailRepository")
 */
class Email
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255)
     * @Assert\NotBlank(message = "Podaj adres e-mail")
     * @Assert\Email (message = "Nieprawidłowy adres e-mail")
     */
    private $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100)
     *
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity = "Person", inversedBy = "mails")
     * @ORM\JoinColumn(name = "person_id", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $person;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return Email
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Email
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set person
     *
     * @param \ContactBundle\Entity\Person $person
     * @return Email
     */
    public function setPerson(\ContactBundle\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return \ContactBundle\Entity\Person 
     */
    public function getPerson()
    {
        return $this->person;
    }
}
