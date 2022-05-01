<?php
require_once 'user.php';

User::dbConnect();
$user = new User(null, 'Aleksandr', 'Gaponenko', '2000-06-15', 0, 'Rechitca');
echo $user->getAge($user->getID());
print json_encode($user->formatPerson('all'));
