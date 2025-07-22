<?php
// src/Form/ItemSelectType.php
namespace App\Form;

use App\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Choix de l'Item dans la table Item
            ->add('item', EntityType::class, [
                'class' => Item::class,
                'choice_label' => 'nom',       // propriété getNom()
                'placeholder'  => '— Choisissez un item —',
                'label'        => 'Item',
            ])
            // Saisie du prix (vous pouvez adapter MoneyType ou IntegerType)
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
                'label'    => 'Prix €',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // On n'attache pas ce formulaire directement à une entité
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
