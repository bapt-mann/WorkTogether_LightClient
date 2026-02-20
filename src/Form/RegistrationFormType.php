<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accountType', ChoiceType::class, [
                'mapped' => false, // Ne correspond à aucune colonne dans la table User
                'choices' => [
                    'Particulier' => 'customer',
                    'Entreprise' => 'company',
                ],
                'expanded' => true, // Affiche des boutons radio
                'multiple' => false,
                'data' => 'customer', // Particulier par défaut
            ])
            ->add('companyName', TextType::class, [
                'mapped' => false,
                'required' => false, // Géré par Javascript
            ])
            ->add('siret', TextType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('firstName')
            ->add('lastName')
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répétez le mot de passe'],
                'invalid_message' => 'Les mots de passe doivent être identiques.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}