<?php

namespace App\Form;

// src/Form/ItemForm.php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use App\Entity\Item;

class ItemForm extends AbstractType
{
    // src/Form/ItemForm.php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom')
        ->add('description')
        ->add('palier')
        ->add('prix')
        ->add('imageFile', FileType::class, [
            'label' => 'Image (JPEG/PNG)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => ['image/jpeg', 'image/png'],
                    'mimeTypesMessage' => 'Téléchargez une image valide (JPEG/PNG)',
                ])
            ],
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}