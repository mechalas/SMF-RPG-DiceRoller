# RPG Dice Roller mod for the Simple Machines Forum

A dice roller for Simple Machines Forum (SMF) with features that are
useful for online role-playing games, also known as play-by-post games.

## Introduction

This mod adds a dice roller to an SMF 2.0.x forum that memebrs can access
using the `[dice]` BBCode. It has the following features:

 * Posts with dice rolls can be previewed before posting.

 * Posts with dice rolls can be edited.

 * Multiple dice rolls can be placed in a dice expression.

 * Allows mulitple + and - modifiers in a dice expression.

 * Dice rolls can be labeled.

 * Support for automatic re-rolling of "critical" die results (e.g. to confirm
   "critical success")

 * Modest anti-cheating mechanism to discourage shenanigans.

## Requirements

This mod supports SMF 2.0.x and has been tested on 2.0.15.

## Basic Usage

The basic syntax for creating a dice roll is:

```
[dice] MdN [/dice]

[dice=label] MdN + a - b ... [/dice]
```

Where `M` is the number of dice and `N` is the number of sides on the
dice. The `M` parameter is optional, and defaults to 1 if ommitted.
This will result in M die rolls, generating numbers from 1 to N, which
are then added together. You can have any number of die rolls in a
dice expression.

The `a` and `b` parameters are '''modifiers''' that add or subtract from
the result. You can have any number of modifiers in a dice expression.

The `label` is optional, and if present will prefix your roll with the
given label text.

### Examples

```
[dice]d20[/dice]
```

Roll a single, 20-sided dice and print the result.

```
[dice]d20+10[/dice]
```

Roll a single, 20-sided dice and add 10 to the result.

```
[dice=damage (sneak attack)]d10+2d6+4-1[/dice]
```

Roll a d10, add a roll of 2d6, add 4, and then subtract 1.

## Advanced Usage

### Confirm a potential critical success

Only one critical roll is allowed in a dice expression.

```dNc```

Roll a dN and roll again to confirm a potential critical success if 
the result = N.

```dNcX```

Roll a dN and roll again to confirm a potential critical success if 
the result >= X.

```dNcXbY```

Roll a dN and roll again to confirm a potential critical success if
the result >= X, and add a bonus of +Y to the confirmation roll.

```dNcXpY```

As above, only apply a penalty of -Y to the confirmation roll.

#### Examples

```
[dice]d20c + 4[/dice]
```

Reroll to confirm a critical result if you roll a 20.

```
[dice]d20c18[/dice] + 4
```

Reroll to confirm a critical result if you roll an 18 or higher.

```
[dice]d20c18b1[/dice] + 4
```

Reroll to confirm a critical result if you roll an 18 or higher, and
add a bonus of +1 to the reroll.

## How it works

The RPG Dice Roller relies on the mt_rand() and mt_srand() functions
in PHP. When a message is started, it uses a deterministic algorithm
to generate a psuedorandom seed. Seeds are specific to the destination
forum so that a die roll in one forum is not impacted by die rolls in
others. This also discourages (but does not prevent) shenanigans and
casual cheating.

When a message is posted, the seed is stored in the SMF database and
recalled when the message is edited. This allows players to edit a post
that contains die rolls.

Cheating is still possible, of course, but it requires deliberate
action from the poster. As with all cooperative games, the assumption
is that people play fairly and don't go out of their way to cheat, or
that they don't do so flagrantly.

### Database Modifications

The mod adds a column named `rpg_dr_seed` to the `smf_messages` table
to store the random seeds.

### Uninstalling

Uninstalling the plugin does not delete the `rpg_dr_seed` column, as
deleting the seed column would cause new seeds to be generated if/when
the mod is re-installed.  This would effectively change any existing
die rolls.

If you want to remove the column because you'll be permanently
uninstalling the mod, or you just don't care, then edit `packing-info.xml`
and uncomment this line before uninstalling the mod:

```
<!--    <code>uninstall_2_0.php</code> -->

```

## Bugs

Dice roll expressions cannot start with a negative number. i.e., `-1+d6`
will result in an error.

