<?php

namespace App\Form;

use App\Entity\Guild;
use App\Entity\GuildItems;
use App\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuildItemsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price')
            ->add('guild', EntityType::class, [
                'class' => Guild::class,
                'choice_label' => 'id',
            ])
            ->add('item', EntityType::class, [
                'class' => Item::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GuildItems::class,
        ]);
    }
}
