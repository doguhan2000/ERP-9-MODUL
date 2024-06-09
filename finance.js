document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('transaction-form');
    const transactionsContainer = document.getElementById('transactions');
    const budgetForm = document.getElementById('budget-form');
    const budgetStatus = document.getElementById('budget-status');
    const taxDetails = document.getElementById('tax-details');
    const tickerContent = document.getElementById('ticker-content');
    let budget = 0;

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        
        const formData = new FormData(form);
        fetch('saveTransaction.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTransactions();
                form.reset(); 
            } else {
                alert('İşlem kaydedilemedi: ' + data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    budgetForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        budget = parseFloat(document.getElementById('budget').value);
        updateBudgetStatus();
    });

    function loadTransactions() {
        fetch('getTransactions.php')
        .then(response => response.json())
        .then(data => {
            console.log(data); 
            transactionsContainer.innerHTML = '';
            taxDetails.innerHTML = '';
            let income = 0;
            let expense = 0;
            let totalTax = 0;

            
            const table = document.createElement('table');
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            table.appendChild(thead);
            table.appendChild(tbody);
            transactionsContainer.appendChild(table);

            const headerRow = document.createElement('tr');
            const headers = ['Tarih', 'Tür', 'Miktar', 'Açıklama', 'Vergi', 'Para Birimi'];
            headers.forEach(headerText => {
                const header = document.createElement('th');
                header.textContent = headerText;
                headerRow.appendChild(header);
            });
            thead.appendChild(headerRow);

            data.forEach(transaction => {
                const row = document.createElement('tr');

                const dateCell = document.createElement('td');
                dateCell.textContent = transaction.tarih;
                row.appendChild(dateCell);

                const typeCell = document.createElement('td');
                typeCell.textContent = transaction.tur === 'gelir' ? 'Gelir' : 'Gider';
                row.appendChild(typeCell);

                const amountCell = document.createElement('td');
                amountCell.textContent = transaction.miktar;
                row.appendChild(amountCell);

                const descriptionCell = document.createElement('td');
                descriptionCell.textContent = transaction.aciklama;
                row.appendChild(descriptionCell);

                const taxCell = document.createElement('td');
                const taxAmount = (transaction.miktar * transaction.vergi_orani / 100).toFixed(2);
                taxCell.textContent = taxAmount;
                row.appendChild(taxCell);

                const currencyCell = document.createElement('td');
                currencyCell.textContent = transaction.para_birimi;
                row.appendChild(currencyCell);

                tbody.appendChild(row);

                totalTax += parseFloat(taxAmount);

                if (transaction.tur === 'gelir') {
                    income += parseFloat(transaction.miktar);
                } else {
                    expense += parseFloat(transaction.miktar);
                }
            });

            
            const taxReport = document.createElement('div');
            taxReport.innerHTML = `<p>Toplam Vergi: ${totalTax.toFixed(2)}</p>`;
            taxDetails.appendChild(taxReport);

            
            const ctx = document.getElementById('transactionsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Gelir', 'Gider'],
                    datasets: [{
                        label: 'Miktar',
                        data: [income, expense],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            updateBudgetStatus();
        })
        .catch(error => console.error('Error:', error));
    }

    function updateBudgetStatus() {
        const totalIncome = Array.from(transactionsContainer.querySelectorAll('td:nth-child(3)'))
            .filter(td => td.previousElementSibling.textContent === 'Gelir')
            .reduce((sum, td) => sum + parseFloat(td.textContent), 0);

        const totalExpense = Array.from(transactionsContainer.querySelectorAll('td:nth-child(3)'))
            .filter(td => td.previousElementSibling.textContent === 'Gider')
            .reduce((sum, td) => sum + parseFloat(td.textContent), 0);

        const remainingBudget = budget - totalExpense;

        budgetStatus.textContent = `Kalan Bütçe: ${remainingBudget.toFixed(2)} (Gelir: ${totalIncome.toFixed(2)}, Gider: ${totalExpense.toFixed(2)})`;
    }

    function loadCurrencyRates() {
        fetch('https://api.genelpara.com/embed/doviz.json')
            .then(response => response.json())
            .then(data => {
                let tickerHtml = '<ul>';
                const currencies = ['USD', 'EUR', 'GBP', 'CHF', 'CAD']; 

                currencies.forEach(currency => {
                    if (data[currency]) {
                        tickerHtml += `<li><span>${currency}</span> <span>Fiyat: ${data[currency].satis}</span> <span>Değişim: ${data[currency].degisim}</span></li>`;
                    }
                });

                tickerHtml += '</ul>';
                tickerContent.innerHTML = tickerHtml;
            })
            .catch(error => console.error('Error:', error));
    }

    loadTransactions();
    loadCurrencyRates();
});
