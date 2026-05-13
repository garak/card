# PHP Card library

[![Latest Stable Version](http://poser.pugx.org/garak/card/v)](https://packagist.org/packages/garak/card)
[![Latest Unstable Version](http://poser.pugx.org/garak/card/v/unstable)](https://packagist.org/packages/garak/card)
[![License](http://poser.pugx.org/garak/card/license)](https://packagist.org/packages/garak/card)
[![PHP Version Require](http://poser.pugx.org/garak/card/require/php)](https://packagist.org/packages/garak/card)
[![Maintainability](https://qlty.sh/gh/garak/projects/card/maintainability.svg)](https://qlty.sh/gh/garak/projects/card)
[![Code Coverage](https://qlty.sh/gh/garak/projects/card/coverage.svg)](https://qlty.sh/gh/garak/projects/card)

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

$card = new Card(Rank::Ace, Suit::Diamonds);
echo $card; // will output "Ad"

$card = new Card(Rank::Seven, Suit::Spades);
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

## Upgrading from version 0.8

`Rank` and `Suit` have been converted from regular classes to backed enums.
The following breaking changes apply.

### Instantiation

| Before | After |
|---|---|
| `new Rank('A')` | `Rank::Ace` |
| `new Rank('T')` | `Rank::Ten` |
| `new Suit('s')` | `Suit::Spades` |
| `new Suit('h')` | `Suit::Hearts` |

When you only have a string at runtime (e.g. from user input or persistence), use the enum's `from()` factory:

```php
$rank = Rank::from('A');  // Rank::Ace
$suit = Suit::from('s');  // Suit::Spades
```

### Invalid values

Previously invalid values threw `\InvalidArgumentException`.
They now throw `\ValueError` (the standard PHP exception for invalid enum values):

```php
// Before
try {
    new Rank('X');
} catch (\InvalidArgumentException $e) { ... }

// After
try {
    Rank::from('X');
} catch (\ValueError $e) { ... }
```

Use `Rank::tryFrom('X')` / `Suit::tryFrom('X')` to get `null` instead of an exception.

### Iterating over all ranks or suits

The public static arrays `Rank::$ranks` and `Suit::$suits` have been removed.
Use the standard enum `cases()` method instead:

```php
// Before
foreach (Rank::$ranks as $symbol => $intValue) { ... }
foreach (Suit::$suits as $symbol => $unicodeChar) { ... }

// After
foreach (Rank::cases() as $rank) {
    $symbol   = $rank->value;    // e.g. 'A'
    $intValue = $rank->getInt(); // e.g. 14
}
foreach (Suit::cases() as $suit) {
    $symbol    = $suit->value;      // e.g. 's'
    $unicodeChar = $suit->toText(); // e.g. '♠'
}
```

`Suit::$jokerColors` has also been removed; joker suits are now `Suit::BlackJoker` and `Suit::RedJoker`.
