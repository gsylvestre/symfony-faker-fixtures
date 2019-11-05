    protected function load<?= ucfirst($class_data->getShortPluralClassName()) ?>(int $num): void
    {
        $this->progress->setMessage("loading <?= mb_strtolower($class_data->getShortPluralClassName()) ?>");
<?php
foreach($class_data->getFields() as $field):
    if ($field->getisAssoc() &&
        $field->getType() !== \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY &&
        $field->getisOwningSide()):
?>
        $all<?= $field->getAssociatedShortPluralClassName() ?> = $this->doctrine->getRepository(<?= $field->getAssociatedShortClassName() ?>::class)->findAll();
<?php
    endif;
endforeach;
?>
        for($i=0; $i<$num; $i++){
<?php $var = "$" . lcfirst($class_data->getShortClassName()) ?>
            <?= $var ?> = new <?= $class_data->getShortClassName() ?>();

<?php
foreach($class_data->getFields() as $field):
    if (!$field->getisAssoc()):
        if ($field->getFieldName() != "id"):
            if (!empty($field->getisSecurityPasswordField())):
?>
            //password
            $plainPassword = "ryanryan";
            $hash = $this->passwordEncoder->encodePassword(<?= $var ?>, $plainPassword);
            <?= $var ?>->setPassword($hash);
<?php continue; ?>
<?php endif; ?>
<?php
            if ($field->getSetter() === null):
?>
            //no setter found for <?= $field->getFieldName() ?>
<?php elseif(empty($field->getFakerMethod())): ?>
<?php if ($class_data->getSecurityUserClass() && $field->getSetter() === "setRoles"): ?>
            //roles
            <?= $var ?>-><?= $field->getSetter() ?>( [$this->faker->randomElement(["ROLE_USER", "ROLE_ADMIN"])] );
<?php else: ?>
            //no faker method found!
            //<?= $var ?>-><?= $field->getSetter() ?>( $this->faker-><?= $field->getFakerMethod() ?> );
<?php endif; ?>
<?php else: ?>
            <?= $var ?>-><?= $field->getSetter() ?>( $this->faker-><?= $field->getFakerMethod() ?> );
<?php
            endif;
        endif;
    endif;
endforeach
?>
<?php
foreach($class_data->getFields() as $field):
    if ($field->getisAssoc() && $field->getisOwningSide()):
        $methodName = sprintf($field->getFakerMethod(), '$all'.$field->getAssociatedShortPluralClassName());
        if (!empty($field->getAdder()) && $field->getType() != \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY):
?>
            /*
            uncomment below to add more than one
            (you might need to increase the total number of <?= $field->getFieldName() ?> to load in LoadAllFixturesCommand.php
            */
            //$numberOf<?= $field->getFieldName() ?> = $this->faker->numberBetween($min = 0, $max = 5);
            //for($n = 0; $n < $numberOf<?= $field->getFieldName() ?>; $n++){
                <?= $var ?>-><?= $field->getAdder() ?>( $this->faker-><?= $methodName ?> );
            //}
<?php
        elseif (!empty($field->getSetter())):
?>
            <?= $var ?>-><?= $field->getSetter() ?>( $this->faker-><?= $methodName ?> );
<?php
        elseif($field->getType() != \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY):
?>
            //oups no method for <?= $field->getFieldName() . "\n" ?>
            //<?= $var ?>...
<?php
        endif;
    endif;
endforeach
?>

            $this->doctrine->getManager()->persist(<?= $var ?>);
            $this->progress->advance();
    }

        $this->doctrine->getManager()->flush();
    }

