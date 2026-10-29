<?php

namespace App\Exceptions;

use RuntimeException;

// Erreur métier dont le message peut être montré tel quel au joueur.
class BookingException extends RuntimeException {}
