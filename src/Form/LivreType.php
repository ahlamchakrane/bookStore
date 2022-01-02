<?php

namespace App\Form;

use App\Entity\Livre;
use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isbn')
            ->add('titre')
            ->add('nombre_page')
            ->add('date_de_parution', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('note')
            ->add('auteur')
            ->add('genres');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livre::class,
        ]);
    }
}
