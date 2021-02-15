<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'required' => false
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'required' => false,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Tapez le mot de passe à nouveau',
                'required' => false],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => false
            ])
            ->add('role_user', ChoiceType::class, [
                'choices' => $this->getRoles(),
                'required' => true,
                'empty_data' => 'ROLE_USER'
            ])
        ;
    }

    public function getRoles()
    {
        return ['Utilisateur' => 'ROLE_USER', 'Administrateur' => 'ROLE_ADMIN'];
    }
}
