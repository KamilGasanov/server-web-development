const display = document.getElementById('display');
const expressionInput = document.getElementById('expression');
const form = document.getElementById('calculator-form');
const valueButtons = document.querySelectorAll('[data-value]');
const actionButtons = document.querySelectorAll('[data-action]');
const keyMappings = {
    p: 'pi',
    e: 'e',
    r: 'sqrt(',
    n: 'ln(',
    g: 'log(',
    s: 'sin(',
    c: 'cos(',
    t: 'tan('
};

function syncExpression(nextValue) {
    display.value = nextValue;
    expressionInput.value = nextValue;
}

function appendValue(value) {
    syncExpression(display.value + value);
}

function clearExpression() {
    syncExpression('');
}

function removeLastCharacter() {
    syncExpression(display.value.slice(0, -1));
}

valueButtons.forEach((button) => {
    button.addEventListener('click', () => {
        appendValue(button.dataset.value || '');
    });
});

actionButtons.forEach((button) => {
    button.addEventListener('click', () => {
        if (button.dataset.action === 'clear') {
            clearExpression();
        }

        if (button.dataset.action === 'backspace') {
            removeLastCharacter();
        }
    });
});

document.addEventListener('keydown', (event) => {
    const allowedKeys = '0123456789+-*/().^!';

    if (allowedKeys.includes(event.key)) {
        event.preventDefault();
        appendValue(event.key);
        return;
    }

    if (keyMappings[event.key]) {
        event.preventDefault();
        appendValue(keyMappings[event.key]);
        return;
    }

    if (event.key === 'Backspace') {
        event.preventDefault();
        removeLastCharacter();
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        clearExpression();
    }

    if (event.key === 'Enter') {
        event.preventDefault();
        form.requestSubmit();
    }
});
