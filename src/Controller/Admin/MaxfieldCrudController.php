<?php

namespace App\Controller\Admin;

use App\Entity\Maxfield;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MaxfieldCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Maxfield::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('name');
        yield AssociationField::new('owner')
            ->autocomplete();
            // ->setQueryBuilder(function (QueryBuilder $qb) {
            //     $qb->andWhere('entity.state = :state')
            //         ->setParameter('state', 1);
            // });;
        // return [
        //     IdField::new('id'),
        //     TextField::new('title'),
        //     TextEditorField::new('description'),
        // ];
    }
}
