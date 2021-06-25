# PHP Card library

<img src="https://user-images.githubusercontent.com/179866/114412093-10e4a700-9bad-11eb-80cf-46e007ff6bde.jpg" alt="https://commons.wikimedia.org/wiki/Category:Playing_cards#/media/File:A_pile_of_playing_cards.jpg">

## Introduction

This library offers few VO classes to use inside Card-related applications:

* `Card`: represent a Card, for example an ace of spades.
* `Rank`: represent the rank value of a Card, for example "A" or "7" ("T" is used for 10, to keep same length).
* `Suit`: represent the card suit, for example spades or diamonds.

Some more classes, more elaborate, are available. They are abstract, and thus require a custom implementation to extend them:

* `Hand`: represent a set of Card objects, usually the ones assigned to a player
* `HandsTrick`: represent a trick of hands (think for example to Poker, when players show their hands to declare a winneer)
* `CardTrick`: represent a trick of cards (think for example to the 4 cards of a Bridge turn)

## Installation

Just use `composer require garak/card`.

## Usage

Example:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\Rank;
use Garak\Card\Suit;

$card = new Card(new Rank('A'), new Suit('d'));
echo $card; // will output "Ad"

$card = new Card(new Rank('7'), new Suit('s'));
echo $card->toText(); // will output "7♠"

$card = Card::fromRankSuit('Kh');
echo $card->toUnicode(); // will output "🂾"
```

You can also get a full deck:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;

$orderedCards = Card::getDeck();
$shuffledCards = Card::getDeck(true);
$doubleDeckWithJokers = Card::getDeck(true, 2, true);
```
