<?php

namespace App\Controller\Admin;

use App\Entity\Waypoint;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class WaypointCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Waypoint::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            Field::new('guid'),
            NumberField::new('lat')
                // ->hideOnIndex()
                ->setFormTypeOption('scale', 6),
            NumberField::new('lon')
                // ->hideOnIndex()
                ->setFormTypeOption('scale', 5),
        ];
    }
}
