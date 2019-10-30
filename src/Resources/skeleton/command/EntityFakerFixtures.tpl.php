    protected function load<?= ucfirst($info['plural_name']) ?>(InputInterface $input, OutputInterface $output): int
    {
        $num = $input->getArgument('num');

        $io = new SymfonyStyle($input, $output);

        $this->truncateTable();

<?php
foreach($info['fields'] as $field):
    if ($field['isAssoc']):
?>
        $all<?= $field['assocShortClassName'] ?>Entities = $this->doctrine->getRepository(<?= $field['assocShortClassName'] ?>::class)->findAll();
<?php
    endif;
endforeach;
?>
        for($i=0; $i<$num; $i++){
<?php $var = "$" . lcfirst($info['short_class_name']) ?>
            <?= $var ?> = new <?= $info['short_class_name'] ?>();

<?php
foreach($info['fields'] as $field):
    if (!$field['isAssoc']):
        if ($field['fieldName'] != "id"):
            if (!empty($field['isSecurityPasswordField'])):
?>
            //password
            $plainPassword = "ryanryan";
            $hash = $this->passwordEncoder->encodePassword(<?= $var ?>, $plainPassword);
            <?= $var ?>->setPassword($hash);
<?php continue; ?>
<?php endif; ?>
<?php
            if ($field['setter'] === null):
?>
            //no setter found for <?= $field['fieldName'] ?>
<?php elseif(empty($field['fakerMethod'])): ?>
<?php if ($info['security_user_class'] && $field['setter'] === "setRoles"): ?>
            //roles
            <?= $var ?>-><?= $field['setter'] ?>(
                [$this->faker->randomElement(["ROLE_USER", "ROLE_ADMIN"])]
            );
<?php else: ?>
            //no faker method found!
            //<?= $var ?>-><?= $field['setter'] ?>(
            //    $this->faker-><?= $field['fakerMethod']
?>

            //);
<?php endif; ?>
<?php else: ?>
            <?= $var ?>-><?= $field['setter'] ?>(
                $this->faker-><?= $field['fakerMethod'] ?>

            );
<?php
            endif;
        endif;
    endif;
endforeach
?>
<?php
foreach($info['fields'] as $field):
    if ($field['isAssoc']):
        $methodName = sprintf($field['fakerMethod'], '$all'.$field['assocShortClassName'].'Entities');
        if (!empty($field['adder']) && $field['type'] != \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY):
?>
            /*
            uncomment below to add more than one
            (you might need to increase the total number of <?= $field['fieldName'] ?> to load in LoadAllFixturesCommand.php
            */
            //$numberOf<?= $field['fieldName'] ?> = $this->faker->numberBetween($min = 0, $max = 5);
            //for($n = 0; $n < $numberOf<?= $field['fieldName'] ?>; $n++){
                <?= $var ?>-><?= $field['adder'] ?>(
                    $this->faker-><?= $methodName
?>

                );
            //}
<?php
        elseif (!empty($field['setter'])):
?>
            <?= $var ?>-><?= $field['setter'] ?>(
                $this->faker-><?= $methodName
?>

            );
<?php
        elseif($field['type'] != \FakerFixtures\Doctrine\DependencyGraph::MANYTOMANY):
?>
            //oups no method for <?= $field['fieldName'] . "\n" ?>
            //<?= $var ?>...
<?php
        endif;
    endif;
endforeach
?>

            $this->manager->persist(<?= $var ?>);
        }

        $this->manager->flush();
        $io->writeln($num . ' "<?= $info['short_class_name'] ?>" loaded!');
        return 0;
    }

