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
* `CardBack`: represents the back color of a card, for example red or blue. This allows distinguishing between multiple decks in games played with more than one deck.
   Its `toText()`/`fromText()` methods map a back to a single character ("r" or "b"), used in string serialization.
* `Color`: the color of a suit (or of a joker), red or black.
* `Deck`: the composition of the cards used in a game: ranks, suits, number of copies, jokers, backs.
* `Cards`: an immutable list of cards with filtering, grouping and sorting helpers.
* `CardBag`: a multiset of cards, to compare sets of cards regardless of their order.
* `Pile`: a mutable stack of cards, like a stock or a discard pile.
* `RankOrder`, `SuitOrder`: the order of ranks and suits in a game (ace high, ace low, ...).
* `CardValues`: how much each card is worth in a game, for scoring or as a numeric rank.
* `CardComparator`: sorts cards by a rank order, a suit order, a trump and a direction.

Some more classes, more elaborate, are available. They are abstract, and thus require a custom implementation to extend them:

* `Hand`: represents a set of Card objects, usually the ones assigned to a player
* `CardsTrick`: represents a trick of cards (think for example the 4 cards of a Bridge turn), with the usual winning rule built in

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

### Multiple Decks

When playing with multiple decks, cards can be distinguished by their back color.
Each deck automatically gets a back color, cycling through the available values if you request more decks than backs:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\CardBack;
use Garak\Card\Rank;
use Garak\Card\Suit;

// Single deck - cards have no back (backward compatible)
$singleDeck = Card::getDeck();
$card = $singleDeck[0];
$card->getBack(); // returns null

// Multiple decks - cards have different backs
$twoDecks = Card::getDeck(num: 2);
// First 52 cards have red back, next 52 have blue back

// Requesting more than two decks reuses the available backs in order
$threeDecks = Card::getDeck(num: 3);
// The third deck uses the red back again

// Cards with same face but different backs are not equal
$redAceOfSpades = new Card(Rank::Ace, Suit::Spades, CardBack::Red);
$blueAceOfSpades = new Card(Rank::Ace, Suit::Spades, CardBack::Blue);
$redAceOfSpades->isEqual($blueAceOfSpades); // false
$redAceOfSpades->isSameFace($blueAceOfSpades); // true

// Cards with/without back are different when one side has a back,
// but you can still compare just the face when needed.
$noBackAceOfSpades = new Card(Rank::Ace, Suit::Spades);
$noBackAceOfSpades->isEqual($redAceOfSpades); // false
$noBackAceOfSpades->isSameFace($redAceOfSpades); // true
$noBackAceOfSpades->getBack(); // returns null
```

### Jokers

Jokers are regular cards with rank `Rank::Joker` and suit `Suit::BlackJoker` or `Suit::RedJoker`.
They have no suit symbol, so `toText()` falls back to the raw value:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\Rank;
use Garak\Card\Suit;

$joker = new Card(Rank::Joker, Suit::BlackJoker);
echo $joker;              // will output "wb"
echo $joker->toText();    // will output "wb"
echo $joker->toUnicode(); // will output "🃏"
```

### Serializing cards with backs

A card string can carry the back as an optional third character (`r` for red, `b` for blue).
The default string form drops the back, to stay compatible with existing persisted data.
Use `toString(withBack: true)` when the back must survive a round-trip:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;

$card = Card::fromRankSuit('Asr');
$card->getBack(); // CardBack::Red

echo $card;                           // will output "As"
echo $card->toString(withBack: true); // will output "Asr"

// The same applies to hands
$hand = MyHand::createFromString('Asr,Kdb');
echo $hand;                           // will output "As,Kd"
echo $hand->toString(withBack: true); // will output "Asr,Kdb"
```

### Piles

A `Pile` is an ordered stack of cards, like a stock, a discard pile, or a dealing shoe.
The last card is the top of the pile.
Unlike `Hand`, a `Pile` is mutable: adding and drawing change it in place.

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\Pile;

$stock = new Pile(Card::getDeck());
$stock->shuffle();
$card = $stock->draw();   // removes and returns the top card
$stock->top();            // peeks at the new top card, or null when empty
count($stock);            // 51

$discard = Pile::createFromString('2c,Kd'); // bottom to top
$discard->add($card);     // the added card is now on top
echo $discard;            // will output "2c,Kd,As" (for an ace of spades)
$discard->has(Card::fromRankSuit('Kd')); // true

$cards = $discard->takeAll(); // returns all cards, bottom to top, and empties the pile
```

Like `Hand`, `toString(withBack: true)` keeps the backs of the cards.

Piles can also be built from cards listed top-first, and drawn or peeked several cards at a time:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\Pile;

$stock = Pile::createFromTop(Card::getDeck()); // the first card of the deck is on top
$hand = $stock->drawMany(13);                  // top card first
$next = $stock->peek(3);                       // the next 3 cards to be drawn, not removed
```

### Decks

`Card::getDeck()` covers the most common cases. `Deck` describes any composition of French-suited cards:
how many copies, how many jokers, which ranks and suits (for stripped decks), and which backs.

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\CardBack;
use Garak\Card\Deck;
use Garak\Card\Rank;

$canasta = new Deck(copies: 2, jokers: 4);
count($canasta); // 108
$cards = $canasta->getCards();  // unshuffled: copy by copy, suit by suit, rank by rank, jokers after each copy
$cards = $canasta->shuffle();
$stock = $canasta->createPile(shuffle: true); // a Pile ready to draw from

$piquet = new Deck(ranks: array_slice(Rank::regular(), 5)); // 32 cards, from seven to ace
$shoe = new Deck(copies: 6, backs: []);                    // 312 identical cards, no backs
$blue = new Deck(copies: 2, backs: [CardBack::Blue]);      // both copies with a blue back
```

Jokers alternate black and red, and are spread over the copies (the first two go to the first copy, and so on).

### Reproducible shuffles

`Card::getDeck()`, `Deck::shuffle()`, `Deck::createPile()` and `Pile::shuffle()` accept a `Random\Randomizer`.
Pass a seeded one to get the same order every time, e.g. in tests:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Random\Engine\Mt19937;
use Random\Randomizer;

$deck = Card::getDeck(shuffle: true, randomizer: new Randomizer(new Mt19937(42)));
```

### Cards

`Cards` is an immutable list of cards with no game meaning attached: the cards on the table, the community cards, a capture.
Every method returning cards returns a new instance.

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\CardComparator;
use Garak\Card\Cards;
use Garak\Card\CardValues;
use Garak\Card\Rank;
use Garak\Card\Suit;

$cards = Cards::fromString('As,Kd,Ad,wb,2c');
count($cards);                          // 5
$cards->has(Card::fromRankSuit('Kd'));  // true
$cards->hasSuit(Suit::Hearts);          // false
$cards->ofRank(Rank::Ace);              // As,Ad
$cards->regular();                      // As,Kd,Ad,2c (no jokers)
$cards->groupBySuit();                  // ['s' => As, 'd' => Kd,Ad, 'b' => wb, 'c' => 2c]
$cards->groupByRank()[Rank::Ace->value]; // As,Ad
$cards->with(Card::fromRankSuit('3c')); // a new list with the card appended
$cards->without(Card::fromRankSuit('wb'));
$cards->sort(new CardComparator());
$cards->regular()->sum(CardValues::aceHigh()); // 14 + 13 + 14 + 2
```

`Hand::toCards()` and `Pile::toCards()` give the same helpers on hands and piles.
`Hand` and `Pile` also gained `hasFace()` and `countFace()`, to look for a card whatever its back,
and `Hand` gained `hasSuit()`, `addMany()` and `playMany()`.

### Card bags

A `CardBag` is a multiset: it counts how many times each card is present, regardless of order.
Cards are told apart by rank, suit and back, so identical faces from different decks count separately.
It is the tool to check that a layout still contains every card of the previous one, or to find what was added.

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\CardBag;
use Garak\Card\Cards;

$before = Cards::fromString('5h,5d,5s')->toBag();
$after = new CardBag(Cards::fromString('5h,5d,5s,6s'));

$before->isSubsetOf($after); // true
$after->diff($before);       // [6s]
$before->equals($after);     // false
$after->countOf(Card::fromRankSuit('5h')); // 1
```

### Rank and suit orders, card values

Games disagree on the order of the ranks (the ace is high in poker, low in rummy, the ten beats the king in pinochle)
and on the points cards are worth. `RankOrder`, `SuitOrder` and `CardValues` make those choices explicit,
with presets for the most common ones:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\CardValues;
use Garak\Card\Rank;
use Garak\Card\RankOrder;
use Garak\Card\SuitOrder;

$order = RankOrder::aceHigh();       // two to ace
$order = RankOrder::aceLow();        // ace to king
$order = new RankOrder([Rank::Nine, Rank::Jack, Rank::Queen, Rank::King, Rank::Ten, Rank::Ace]); // pinochle
$order->compare(Rank::Ten, Rank::King); // 1
$order->indexOf(Rank::Nine);            // 0
$order->next(Rank::Nine);               // Rank::Jack
$order->highest($cards);                // the highest card of a list

SuitOrder::standard(); // clubs, diamonds, hearts, spades

$values = CardValues::aceHigh();  // 2 to 14, the former Rank::getInt()
$values = CardValues::aceLow();   // 1 to 13
$values = new CardValues(['A' => 11, '3' => 10, 'K' => 4, 'Q' => 3, 'J' => 2], default: 0); // by rank
$values = (new CardValues(default: 0))->withCard(Card::fromRankSuit('Qs'), 13);          // a single card
$values->of(Card::fromRankSuit('Qs')); // 13
$values->sum($cards);
```

A card with no value (typically a joker) throws, unless a default is given.

### Sorting

`CardComparator` combines a rank order, a suit order, an optional trump and a direction, and can be used
wherever a callable is expected. Cards whose rank is not in the order (jokers) always go last.

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\CardComparator;
use Garak\Card\RankOrder;
use Garak\Card\Suit;

$bySuit = new CardComparator();                                  // by suit, then by rank, ace high, ascending
$poker = new CardComparator(suitFirst: false, descending: true); // by rank, highest first
$bridge = (new CardComparator())->withTrump(Suit::Spades)->withDescending(); // trumps first
$rummy = new CardComparator(RankOrder::aceLow());

$sorted = $bridge->sort($cards); // a sorted copy
usort($cards, $poker);
```

### Tricks

`CardsTrick` now comes with the rule shared by most trick-taking games: the highest trump wins when at least one
was played, otherwise the highest card of the led suit. Extend it and override `getRankOrder()` for another order
of the ranks, or `getWinningCard()` for other rules. The keys of the cards identify the players.

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;
use Garak\Card\CardsTrick;
use Garak\Card\Suit;

final class Trick extends CardsTrick
{
}

$trick = new Trick(['N' => Card::fromRankSuit('5h'), 'E' => Card::fromRankSuit('Kh'), 'S' => Card::fromRankSuit('2s')]);
$trick->getLedSuit();                 // Suit::Hearts
$trick->getWinningCard(null);         // Kh
$trick->getWinningKey(Suit::Spades);  // 'S'
$trick->canPlay($card, $hand);        // false when the hand could follow the led suit and the card does not
```

### Hidden cards

When a card must be shown to someone who is not allowed to see its face (an opponent's hand, a face-down stock),
`toHiddenString()` replaces the rank and suit with `??` and keeps the back, if any:

```php
<?php

require 'vendor/autoload.php';

use Garak\Card\Card;

Card::fromRankSuit('Asr')->toHiddenString(); // "??r"
Card::fromRankSuit('As')->toHiddenString();  // "??"
$hand->toHiddenString();                     // "??r,??r,??r"
```

`Hand`, `Pile` and `Cards` all have it.

## Upgrading to version 0.13 (preparing for 1.0)

Version 0.13 is backward compatible. It deprecates what will be removed or changed in 1.0:

| Deprecated | Replacement |
|---|---|
| `Rank::getInt()` | `CardValues::aceHigh()->ofRank()` for the same numbers, or a `RankOrder` to compare ranks |
| `Suit::getInt()` | a `SuitOrder` |
| `Hand::deal()` | a `Deck`, then `Pile::drawMany()` for each hand |
| `Hand::isValid()` | `Cards::fromString()`, catching its exceptions |
| `HandsTrick` | your own evaluator, with a `CardValues` or a `RankOrder` |
| `Rank::getValue()`, `Suit::getName()` | `->value` (deprecated since 0.9) |

Also note, for 1.0:

* `Hand::add()` and `Hand::play()` will reindex the cards of the new hand (keys will be `0`, `1`, ...), like `Pile` already does.
  Do not rely on the keys of `Hand::getCards()`.
* The abstract constructor of `Hand`, with its `$checking` and `$sorting` callables, will be replaced by a concrete one
  with two overridable hooks: `check()` (throw on an invalid starting hand) and `compare()` (a comparator, see `CardComparator`).
  Subclasses will no longer need to copy the constructor. Start moving your sorting logic to a `CardComparator` now.
* `CardsTrick::getWinningCard()` is no longer abstract: if your subclass implements the usual rule, you can drop the override.

## Upgrading from version 0.11

`Hand::add()` and `Hand::play()` no longer pass the current hand's sorting callback to the new hand.
Your constructor is called with `null` for `$sorting` and must provide the default sorter itself.
This was needed because a sorter defined as a closure bound to `$this` kept sorting the old hand instead of the new one.
Pass a callback explicitly as the second argument if you need a different sorter for the new hand.

`Suit::toText()` and `Card::toText()` no longer throw a `LogicException` for jokers.
They return the raw value instead (e.g. `wb` for the black joker).

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
