<?php

namespace App\Form;

use App\Entity\News;
use App\Enum\NewsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;      // Symfony >= 6.2
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Titre de la nouvelle'],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'property_path' => 'content', // <-- mappe vers la propriété "content"
            ])
            ->add('type', EnumType::class, [
                'class' => NewsType::class,
                'label' => 'Type',
                'choice_label' => static function(NewsType $case): string {
                    // Affiche la valeur lisible définie dans l’enum
                    return $case->value;
                },
                'placeholder' => '— Choisir —',
            ]);
            // Champ 'emetteur' retiré : toujours l'utilisateur connecté
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
