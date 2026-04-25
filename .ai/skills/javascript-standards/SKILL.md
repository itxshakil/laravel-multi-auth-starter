---
name: javascript-standards
description: Apply JavaScript and TypeScript coding style conventions when creating, editing, reviewing, or refactoring .js, .ts, .jsx, .tsx, or .vue files.
---

# JavaScript & TypeScript Standards

## Variable Declarations

Use `const` by default. Use `let` only when the variable will be reassigned. Never use `var`.

```ts
// Good
const user = getUser()
const items = []
items.push('a')  // mutating the object is fine with const

let count = 0
count++  // reassignment requires let

// Avoid
var name = 'Alice'
```

## Equality

Always use strict equality (`===` and `!==`). Never use loose equality (`==` or `!=`). If unsure of the type, cast it first.

```ts
// Good
if (value === null) { }
if (status !== 'active') { }

// Avoid
if (value == null) { }
```

## Functions

Use the `function` keyword for named, top-level functions. Use arrow functions for inline callbacks and terse single-expression operations.

```ts
// Good — named function
function formatDate(date: Date): string {
    return date.toISOString()
}

// Good — arrow for callback
const sorted = items.sort((a, b) => a.name.localeCompare(b.name))

// Avoid — arrow for named top-level function
const formatDate = (date: Date): string => {
    return date.toISOString()
}
```

## Object Method Shorthand

Use object method shorthand syntax; never assign function expressions to object properties.

```ts
// Good
const handler = {
    handleClick(event: MouseEvent) {
        // ...
    },
}

// Avoid
const handler = {
    handleClick: function(event: MouseEvent) {
        // ...
    },
}
```

## Destructuring

Use destructuring assignment for arrays and objects. Use default values where the property may be absent.

```ts
// Good
const [hours, minutes] = time.split(':')

const { name, email, role = 'member' } = user

// Avoid
const parts = time.split(':')
const hours = parts[0]
const minutes = parts[1]
```

## Variable Names

Do not abbreviate variable names in multi-line functions. Use full, descriptive names. Short single-letter names are acceptable only in terse single-line arrow functions where context is obvious (e.g., `items.map(i => i.id)`).

```ts
// Good
function processPayment(order: Order, paymentMethod: PaymentMethod) {
    // ...
}

// Avoid
function processPayment(o: Order, pm: PaymentMethod) {
    // ...
}
```
