# PHP Card library

[![Latest Stable Version](http://poser.pugx.org/garak/card/v)](https://packagist.org/packages/garak/card)
[![Latest Unstable Version](http://poser.pugx.org/garak/card/v/unstable)](https://packagist.org/packages/garak/card)
[![License](http://poser.pugx.org/garak/card/license)](https://packagist.org/packages/garak/card)
[![PHP Version Require](http://poser.pugx.org/garak/card/require/php)](https://packagist.org/packages/garak/card)
[![Maintainability](https://api.codeclimate.com/v1/badges/28c8c9ee8607b93c4727/maintainability)](https://codeclimate.com/github/garak/card/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/28c8c9ee8607b93c4727/test_coverage)](https://codeclimate.com/github/garak/card/test_coverage)

<img src="https://user-images.githubusercontent.com/179866/114412093-10e4a700-9bad-11eb-80cf-46e007ff6bde.jpg" alt="https://commons.wikimedia.org/wiki/Category:Playing_cards#/media/File:A_pile_of_playing_cards.jpg">

## Introduction

This library offers a few VO classes to use inside Card-related applications:

* `Card`: represents a Card, for example an ace of spades.
* `Rank`: represents the rank value of a Card, for example "A" or "7" ("T" is used for 10, to keep the same length).
* `Suit`: represents the card suit, for example spades or diamonds.

Some more classes, more elaborate, are available. They are abstract, and thus require a custom implementation to extend them:

* `Hand`: represents a set of Card objects, usually the ones assigned to a player
* `HandsTrick`: represents a trick of hands (think for example Poker, when players show their hands to declare a winner)
* `CardTrick`: represents a trick of cards (think for example the 4 cards of a Bridge turn)

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
$shuffledCards = Card::getDeck(shuffle: true);
$doubleDeckWithJokers = Card::getDeck(shuffle: true, num: 2, allowJokers: true);
```
