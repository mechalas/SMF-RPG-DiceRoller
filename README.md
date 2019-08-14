# RPG Dice Roller mod for the Simple Machines Forum

A dice roller for Simple Machines Forum (SMF) with features that are
useful for online role-playing games, also known as play-by-post games.

## Introduction

This mod adds a dice roller to an SMF 2.0.x forum that memebrs can access
using the `[dice]` BBCode. It has the following features:

 * Posts with dice rolls can be previewed before posting.

 * Posts with dice rolls can be edited.

 * Allows multiple dice rolls in a dice expression.

 * Allows mulitple + and - modifiers in a dice expression.

 * Dice rolls can be labeled.

 * Support for automatic re-rolling of "critical" die results (e.g. to confirm
   "critical success")

 * Modest anti-cheating mechanism to discourage shenangians.

## Requirements

This mod supports SMF 2.0.x and has been tested on 2.0.15 and up.

## Basic Usage

The basic syntax for creating a dice roll is:

```
[dice] MdN [/dice]

[dice=label] MdN + a - b ... [/dice]
```

The `label` is optional, and if present will prefix your roll with the
given label text.

Where `M` is the number if dice and `N` is the number of sides on the
dice. The `M` parameter is optional, and defaults to 1 if ommitted.
This will result in M die rolls, generating numbers from 1 to N, which
are then added together. You can have any number of die rolls in a
dice expression.

The `a` and `b` parameters are '''modifiers''' that add or subtract from
the result. You can have any number of modifiers in a dice expression.

### Examples

```
[dice]d20[/dice]
```

Roll a single, 20-sided dice and print the result.

[dice]d20+10[/dice]

Roll a single, 20-sided dice and add 10 to the result.

```
[dice=damage (sneak attack)]d10+2d6+4-1[/dice]
```

Roll a d10, add a roll of 2d6, add 4, and then subtract 1.

## Advanced Usage

### Confirm a potential critical result

Only one critical roll is allowed in a dice expression.

```dNc```

Roll a dN and roll again to confirm a potential critical success if the result = N.

```dNcX```

Roll a dN and roll again to confirm a potential critical success if the result >= X.

```dNcXbY```

Roll a dN and roll again to confirm a potential critical success if the result >= X,
and add a bonus of +Y to the confirmation roll.

```dNcXpY```

As above, only apply a penalty of -Y to the confirmation roll.

#### Examples

```[dice]d20c + 4[/dice]```

Reroll to confirm a critical result if you roll a 20.

```[dice]d20c18[/dice] + 4```

Reroll to confirm a critical result if you roll an 18 or higher.

```[dice]d20c18b1[/dice] + 4```

Reroll to confirm a critical result if you roll an 18 or higher, and add a bonus 
of +1 to the reroll.

## Bugs

Dice roll expressions cannot start with a negative number. i.e., `-1+d6` will
result in an error.

