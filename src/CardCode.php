<?php

namespace Garak\Card;

/**
 * @internal
 */
enum CardCode: string
{
    case TwoClubs = '2c';
    case ThreeClubs = '3c';
    case FourClubs = '4c';
    case FiveClubs = '5c';
    case SixClubs = '6c';
    case SevenClubs = '7c';
    case EightClubs = '8c';
    case NineClubs = '9c';
    case TenClubs = 'Tc';
    case JackClubs = 'Jc';
    case QueenClubs = 'Qc';
    case KingClubs = 'Kc';
    case AceClubs = 'Ac';
    case TwoDiamonds = '2d';
    case ThreeDiamonds = '3d';
    case FourDiamonds = '4d';
    case FiveDiamonds = '5d';
    case SixDiamonds = '6d';
    case SevenDiamonds = '7d';
    case EightDiamonds = '8d';
    case NineDiamonds = '9d';
    case TenDiamonds = 'Td';
    case JackDiamonds = 'Jd';
    case QueenDiamonds = 'Qd';
    case KingDiamonds = 'Kd';
    case AceDiamonds = 'Ad';
    case TwoHearts = '2h';
    case ThreeHearts = '3h';
    case FourHearts = '4h';
    case FiveHearts = '5h';
    case SixHearts = '6h';
    case SevenHearts = '7h';
    case EightHearts = '8h';
    case NineHearts = '9h';
    case TenHearts = 'Th';
    case JackHearts = 'Jh';
    case QueenHearts = 'Qh';
    case KingHearts = 'Kh';
    case AceHearts = 'Ah';
    case TwoSpades = '2s';
    case ThreeSpades = '3s';
    case FourSpades = '4s';
    case FiveSpades = '5s';
    case SixSpades = '6s';
    case SevenSpades = '7s';
    case EightSpades = '8s';
    case NineSpades = '9s';
    case TenSpades = 'Ts';
    case JackSpades = 'Js';
    case QueenSpades = 'Qs';
    case KingSpades = 'Ks';
    case AceSpades = 'As';
    case BlackJoker = 'wb';
    case RedJoker = 'wr';

    public function unicode(): string
    {
        return [
            '2c' => '🃒',
            '3c' => '🃓',
            '4c' => '🃔',
            '5c' => '🃕',
            '6c' => '🃖',
            '7c' => '🃗',
            '8c' => '🃘',
            '9c' => '🃙',
            'Tc' => '🃚',
            'Jc' => '🃛',
            'Qc' => '🃝',
            'Kc' => '🃞',
            'Ac' => '🃑',
            '2d' => '🃂',
            '3d' => '🃃',
            '4d' => '🃄',
            '5d' => '🃅',
            '6d' => '🃆',
            '7d' => '🃇',
            '8d' => '🃈',
            '9d' => '🃉',
            'Td' => '🃊',
            'Jd' => '🃋',
            'Qd' => '🃍',
            'Kd' => '🃎',
            'Ad' => '🃁',
            '2h' => '🂲',
            '3h' => '🂳',
            '4h' => '🂴',
            '5h' => '🂵',
            '6h' => '🂶',
            '7h' => '🂷',
            '8h' => '🂸',
            '9h' => '🂹',
            'Th' => '🂺',
            'Jh' => '🂻',
            'Qh' => '🂽',
            'Kh' => '🂾',
            'Ah' => '🂱',
            '2s' => '🂢',
            '3s' => '🂣',
            '4s' => '🂤',
            '5s' => '🂥',
            '6s' => '🂦',
            '7s' => '🂧',
            '8s' => '🂨',
            '9s' => '🂩',
            'Ts' => '🂪',
            'Js' => '🂫',
            'Qs' => '🂭',
            'Ks' => '🂮',
            'As' => '🂡',
            'wb' => '🃏',
            'wr' => '🂿',
        ][$this->value];
    }
}
