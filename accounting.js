document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('transaction-form');
    const transactionsContainer = document.getElementById('transactions').getElementsByTagName('tbody')[0];
    const debtForm = document.getElementById('debt-form');
    const debtsContainer = document.getElementById('debts').getElementsByTagName('tbody')[0];
    const paymentForm = document.getElementById('payment-form');
    const paymentsContainer = document.getElementById('payments').getElementsByTagName('tbody')[0];
    const chequeForm = document.getElementById('cheque-form');
    const chequesContainer = document.getElementById('cheques').getElementsByTagName('tbody')[0];
    const interestRateContent = document.getElementById('interest-rate-content');

    let transactions = [];
    let debts = [];
    let payments = [];
    let cheques = [];

    const interestRates = [
        { text: 'Faiz Oranları' },
        { bank: 'Akbank', rate: '1.5%' },
        { bank: 'YapiKredi', rate: '1.7%' },
        { bank: 'Fibabanka', rate: '1.8%' },
        { bank: 'Türkiye İş Bankası', rate: '2.0%' },
        { bank: 'VakifBank', rate: '1.6%' }
    ];

    function updateInterestRates() {
        let content = `<strong>${interestRates[0].text}:</strong> `;
        interestRates.slice(1).forEach(rate => {
            content += `<span>${rate.bank}: ${rate.rate} &nbsp;&nbsp;&nbsp;</span>`;
        });
        interestRateContent.innerHTML = content;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const transaction = {
            date: document.getElementById('date').value,
            type: document.getElementById('type').value,
            amount: parseFloat(document.getElementById('amount').value),
            description: document.getElementById('description').value,
            tax: parseFloat(document.getElementById('tax').value),
            currency: document.getElementById('currency').value
        };

        transactions.push(transaction);
        displayTransactions();
        form.reset();
    });

    debtForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const debt = {
            supplierName: document.getElementById('supplier-name').value,
            invoiceNumber: document.getElementById('invoice-number').value,
            amount: parseFloat(document.getElementById('debt-amount').value),
            dueDate: document.getElementById('due-date').value
        };

        debts.push(debt);
        displayDebts();
        debtForm.reset();
    });

    paymentForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const payment = {
            paymentDate: document.getElementById('payment-date').value,
            paymentAmount: parseFloat(document.getElementById('payment-amount').value),
            paymentMethod: document.getElementById('payment-method').value
        };

        payments.push(payment);
        displayPayments();
        paymentForm.reset();
    });

    chequeForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const cheque = {
            chequeNumber: document.getElementById('cheque-number').value,
            chequeAmount: parseFloat(document.getElementById('cheque-amount').value),
            chequeDate: document.getElementById('cheque-date').value
        };

        cheques.push(cheque);
        displayCheques();
        chequeForm.reset();
    });

    function displayTransactions() {
        transactionsContainer.innerHTML = '';
        let totalTax = 0;

        transactions.forEach(transaction => {
            const row = transactionsContainer.insertRow();
            row.insertCell(0).textContent = transaction.date;
            row.insertCell(1).textContent = transaction.type;
            row.insertCell(2).textContent = transaction.amount.toFixed(2);
            row.insertCell(3).textContent = transaction.description;
            row.insertCell(4).textContent = transaction.tax.toFixed(2);
            row.insertCell(5).textContent = transaction.currency;

            totalTax += transaction.amount * (transaction.tax / 100);
        });

        displayTaxReport(totalTax);
    }

    function displayDebts() {
        debtsContainer.innerHTML = '';

        debts.forEach(debt => {
            const row = debtsContainer.insertRow();
            row.insertCell(0).textContent = debt.supplierName;
            row.insertCell(1).textContent = debt.invoiceNumber;
            row.insertCell(2).textContent = debt.amount.toFixed(2);
            row.insertCell(3).textContent = debt.dueDate;
        });
    }

    function displayPayments() {
        paymentsContainer.innerHTML = '';

        payments.forEach(payment => {
            const row = paymentsContainer.insertRow();
            row.insertCell(0).textContent = payment.paymentDate;
            row.insertCell(1).textContent = payment.paymentAmount.toFixed(2);
            row.insertCell(2).textContent = payment.paymentMethod;
        });
    }

    function displayCheques() {
        chequesContainer.innerHTML = '';

        cheques.forEach(cheque => {
            const row = chequesContainer.insertRow();
            row.insertCell(0).textContent = cheque.chequeNumber;
            row.insertCell(1).textContent = cheque.chequeAmount.toFixed(2);
            row.insertCell(2).textContent = cheque.chequeDate;
        });
    }

    function displayTaxReport(totalTax) {
        const taxDetails = document.getElementById('tax-details');
        taxDetails.innerHTML = `<p>Toplam Vergi: ${totalTax.toFixed(2)}</p>`;
    }

    updateInterestRates();
});
