<?php

namespace App\Framework\Form;

use App\Data\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder->add('email');
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
                'data_class' => User::class,
                'csrf_protection' => false,
                'validation_groups' => false,
                'cascade_validation' => true,
            ]
        );
    }

    public function getBlockPrefix(): string {
        return 'app_user';
    }
}