<form action="/charge" method="POST">
    {{ csrf_field() }}
    <label for="amount">
        Amount (in cents):
        <input type="text" name="amount" id="amount">
    </label>

    <label for="email">
        Email:
        <input type="text" name="email" id="email">
    </label>

    <label for="card-element">
        Credit or debit card
    </label>
    <div id="card-element">
        <!-- A Stripe Element will be inserted here. -->
    </div>

    <!-- Used to display form errors. -->
    <div id="card-errors" role="alert"></div>

    <button type="submit">Submit Payment</button>
</form>

<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>

<script>
    // Your Stripe public key
    var stripe = Stripe('pk_test_51PK1ToDw1ysiixXEQD4qGVmtXF29OFXja6r6hwa8IaXFyX3FEkb9RlvBk4D4X9vQkH85Gwn8AIS2fXuwz4IQEX5k0097xo12Td');

    // Create an instance of Elements
    var elements = stripe.elements();

    // Custom styling can be passed to options when creating an Element
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };

    // Create an instance of the card Element
    var card = elements.create('card', {style: style});

    // Add an instance of the card Element into the `card-element` div
    card.mount('#card-element');

    // Handle real-time validation errors from the card Element
    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission
    var form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        stripe.createToken(card).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server
                stripeTokenHandler(result.token);
            }
        });
    });

    // Submit the form with the Stripe token
    function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        var form = document.querySelector('form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);

        // Submit the form
        form.submit();
    }
</script>
