let clients = [];
let editIndex = -1;

const clientForm = document.getElementById('clientForm');
const tableBody = document.querySelector('#clientsTable tbody');
const searchInput = document.getElementById('searchInput');

clientForm.addEventListener('submit', function (e) {
  e.preventDefault();
  const nom = document.getElementById('nom').value;
  const email = document.getElementById('email').value;
  const telephone = document.getElementById('telephone').value;
  const adresse = document.getElementById('adresse').value;

  const client = { nom, email, telephone, adresse };

  if (editIndex === -1) {
    clients.push(client);
  } else {
    clients[editIndex] = client;
    editIndex = -1;
  }

  clientForm.reset();
  afficherClients();
});

function afficherClients() {
  const filtre = searchInput.value.toLowerCase();
  tableBody.innerHTML = '';

  clients
    .filter(c => c.nom.toLowerCase().includes(filtre))
    .forEach((client, index) => {
      const row = tableBody.insertRow();
      row.innerHTML = `
        <td>${client.nom}</td>
        <td>${client.email}</td>
        <td>${client.telephone}</td>
        <td>${client.adresse}</td>
        <td class="actions">
          <button onclick="modifierClient(${index})">Modifier</button>
          <button onclick="supprimerClient(${index})">Supprimer</button>
        </td>
      `;
    });
}

function modifierClient(index) {
  const client = clients[index];
  document.getElementById('nom').value = client.nom;
  document.getElementById('email').value = client.email;
  document.getElementById('telephone').value = client.telephone;
  document.getElementById('adresse').value = client.adresse;
  editIndex = index;
}

function supprimerClient(index) {
  if (confirm("Voulez-vous vraiment supprimer ce client ?")) {
    clients.splice(index, 1);
    afficherClients();
  }
}

searchInput.addEventListener('input', afficherClients);
