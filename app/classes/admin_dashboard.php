<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Admin - AccordEnergie</title>
    <link rel="stylesheet" href="/css/adminn_css">
    <style>
        section {
            display: none;
        }
    </style>
</head>
<body>

    <h1>Tableau de Bord Admin</h1>
    
    <div class="navigation">
    <button id="btnGestionInterventions">Gestion des Interventions</button>
    <button id="btnGestionUtilisateurs">Gestion des Utilisateurs</button>
    <button id="btnGestionStatus">Gestion des Status</button>
    <button id="btnGestionUrgences">Gestion des Urgences</button>
</div>


    <section id="gestionInterventions">
        <h2>Gestion des Interventions</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Client</th>
                    <th>Intervenants</th>
                    <th>Status</th>
                    <th>Urgence</th>
                    <th>Date Planifiée</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            {% for intervention in interventions %}
<tr>
    <td>{{ intervention.title }}</td>
    <td>{{ intervention.description }}</td>
    <td>{{ intervention.client_id }}</td>
    <td>{{ intervention.intervenants_ids }}</td> 
    <td>{{ intervention.status }}</td>
    <td>{{ intervention.urgency_level }}</td>
    <td>{{ intervention.date_planned }}</td>
    <td>
        <form action="index.php" method="get">
    <input type="hidden" name="action" value="editInterventionForm">
    <input type="hidden" name="intervention_id" value="{{ intervention.intervention_id }}">
    <button type="submit">Edit</button>
</form>
        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="deleteIntervention">
            <input type="hidden" name="intervention_id" value="{{ intervention.intervention_id }}">
            <button type="submit">Delete</button>
        </form>
        <a href="javascript:void(0)" onclick="addComment({{ intervention.intervention_id }})">Commenter</a>
        <a href="javascript:void(0)" onclick="showComments({{ intervention.intervention_id }})">Afficher les commentaires</a>
    </td>
</tr>
<div id="comments-container-{{ intervention.intervention_id }}" style="display:none;">
    <h3>Commentaires</h3>
    <ul id="comments-list-{{ intervention.intervention_id }}"></ul>
</div>
{% endfor %}





            </tbody>
        </table>
    </section>

    <section id="gestionUtilisateurs">
        <h2>Gestion des Utilisateurs</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {% for user in users %}
                <tr>
                    <td>{{ user.username }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.role_id }}</td>
                    <td>
                       <form action="index.php" method="get">
    <input type="hidden" name="action" value="editUserForm">
    <input type="hidden" name="user_id" value="{{ user.user_id }}">
    <button type="submit">Edit</button>
</form>

                        <form action="index.php" method="get">
    <input type="hidden" name="action" value="deleteUser">
    <input type="hidden" name="user_id" value="{{ user.user_id }}">
    <button type="submit">Delete</button>
</form>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </section>
<section id="gestionStatus">
    <h2>Gestion des Status</h2>
    <button id="btnAjouterStatus">Ajouter un nouveau status</button>
    <div id="formAjouterStatus" style="display:none;">
        <form action="index.php" method="post">
            <input type="hidden" name="action" value="createStatus">
            <label for="nomStatus">Nom du Status :</label>
            <input type="text" id="nomStatus" name="status" required>
            <button type="submit">Créer Status</button>
        </form>
    </div>
    <table border="1">
        <thead>
            <tr>
                <th>ID Status</th>
                <th>Nom du Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {% for status in statuses %}
            <tr>
                <td>{{ status.ids }}</td>
                <td>{{ status.status }}</td>
                <td>
                   <form action="index.php" method="post">
    <input type="hidden" name="action" value="editStatus">
    <input type="hidden" name="ids" value="{{ status.ids }}">
    <label for="newStatus{{ status.ids }}">Nouveau statut :</label>
    <input type="text" id="newStatus{{ status.ids }}" name="newStatus" value="{{ status.status }}" required>
    <button type="submit">Mettre à jour</button>
</form>

                   <!-- Exemple dans votre fichier Twig pour chaque statut listé -->
<form action="index.php" method="post">
    <input type="hidden" name="action" value="deleteStatus">
    <input type="hidden" name="ids" value="{{ status.ids }}">
    <button type="submit">Supprimer</button>
</form>

                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
</section>
<section id="gestionUrgences">
    <h2>Gestion des Urgences</h2>
    <button id="btnAjouterUrgence">Ajouter une nouvelle urgence</button>
    <div id="formAjouterUrgence" style="display:none;">
       <form action="index.php" method="post">
    <input type="hidden" name="action" value="addUrgence">
    <label for="urgence">Nom de l'Urgence :</label>
    <input type="text" id="urgence" name="urgence" required>
    <button type="submit">Ajouter Urgence</button>
</form>

    </div>
    <table border="1">
        <thead>
            <tr>
                <th>ID Urgence</th>
                <th>Nom de l'Urgence</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {% for urgence in urgences %}
            <tr>
                <td>{{ urgence.idu }}</td>
                <td>{{ urgence.urgency_level }}</td>
                <td>
                    <form action="index.php" method="post">
    <input type="hidden" name="action" value="editUrgence">
    <input type="hidden" name="idu" value="{{ urgence.idu }}">
    <label for="urgence">Niveau d'urgence :</label>
    <input type="text" id="urgence" name="urgence" value="{{ urgence.urgency_level }}" required>
    <button type="submit">Mettre à jour</button>
</form>

                   <form action="index.php" method="post">
    <input type="hidden" name="action" value="deleteUrgence">
    <input type="hidden" name="id" value="{{ urgence.idu }}">
    <button type="submit">Supprimer</button>
</form>

                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
</section>

   

    <script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('th').forEach(headerCell => {
        headerCell.addEventListener('click', () => {
            const tableElement = headerCell.parentElement.parentElement.parentElement;
            const headerIndex = Array.prototype.indexOf.call(headerCell.parentElement.children, headerCell);
            const isAscending = headerCell.classList.contains('asc');
            const directionModifier = isAscending ? 1 : -1;
            headerCell.classList.toggle('asc', !isAscending);
            headerCell.classList.toggle('desc', isAscending);
            const rows = Array.from(tableElement.querySelectorAll('tbody > tr'));
            rows.sort((a, b) => {
                const aText = a.children[headerIndex].textContent.trim();
                const bText = b.children[headerIndex].textContent.trim();
                return aText.localeCompare(bText) * directionModifier;
            });
            while (tableElement.querySelector('tbody').firstChild) {
                tableElement.querySelector('tbody').removeChild(tableElement.querySelector('tbody').firstChild);
            }
            tableElement.querySelector('tbody').append(...rows);
        });
    });
});

        document.addEventListener("DOMContentLoaded", function() {
    var btnGestionInterventions = document.getElementById('btnGestionInterventions');
    var btnGestionUtilisateurs = document.getElementById('btnGestionUtilisateurs');
    var btnGestionStatus = document.getElementById('btnGestionStatus');
    var btnGestionUrgences = document.getElementById('btnGestionUrgences');
    
    var sectionGestionInterventions = document.getElementById('gestionInterventions');
    var sectionGestionUtilisateurs = document.getElementById('gestionUtilisateurs');
    var sectionGestionStatus = document.getElementById('gestionStatus');
    var sectionGestionUrgences = document.getElementById('gestionUrgences');

    var btnAjouterStatus = document.getElementById('btnAjouterStatus');
    var formAjouterStatus = document.getElementById('formAjouterStatus');
    
    var btnAjouterUrgence = document.getElementById('btnAjouterUrgence');
    var formAjouterUrgence = document.getElementById('formAjouterUrgence');

    btnAjouterUrgence.addEventListener('click', function() {
        formAjouterUrgence.style.display = 'block';
    });

    btnAjouterStatus.addEventListener('click', function() {
        formAjouterStatus.style.display = 'block';
    });

    btnGestionInterventions.addEventListener('click', function() {
        sectionGestionInterventions.style.display = 'block';
        sectionGestionUtilisateurs.style.display = 'none';
        sectionGestionStatus.style.display = 'none';
        sectionGestionUrgences.style.display = 'none';
    });

    btnGestionUtilisateurs.addEventListener('click', function() {
        sectionGestionInterventions.style.display = 'none';
        sectionGestionUtilisateurs.style.display = 'block';
        sectionGestionStatus.style.display = 'none';
        sectionGestionUrgences.style.display = 'none';
    });

    btnGestionStatus.addEventListener('click', function() {
        sectionGestionInterventions.style.display = 'none';
        sectionGestionUtilisateurs.style.display = 'none';
        sectionGestionStatus.style.display = 'block';
        sectionGestionUrgences.style.display = 'none';
    });

    btnGestionUrgences.addEventListener('click', function() {
        sectionGestionInterventions.style.display = 'none';
        sectionGestionUtilisateurs.style.display = 'none';
        sectionGestionStatus.style.display = 'none';
        sectionGestionUrgences.style.display = 'block';
    });
});
function addComment(interventionId) {
    var commentContent = prompt("Entrez votre commentaire :");
    if (commentContent) {
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=postComment&intervention_id=${interventionId}&content=${encodeURIComponent(commentContent)}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                showComments(interventionId); // Actualiser la liste des commentaires
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
}



function showComments(interventionId) {
    fetch(`index.php?action=getComments&interventionId=${interventionId}`)
    .then(response => response.json())
    .then(data => {
        const commentsList = document.getElementById(`comments-list-${interventionId}`);
        if (!commentsList) {
            console.error('Élément de liste de commentaires introuvable:', `comments-list-${interventionId}`);
            return;
        }
        commentsList.innerHTML = '';
        data.forEach(comment => {
            const li = document.createElement('li');
            li.textContent = comment.content;
            commentsList.appendChild(li);
        });
        const commentsContainer = document.getElementById(`comments-container-${interventionId}`);
        if (!commentsContainer) {
            console.error('Conteneur de commentaires introuvable:', `comments-container-${interventionId}`);
            return;
        }
        commentsContainer.style.display = 'block';
    })
    .catch(error => console.error('Erreur:', error));
}

    </script>
    {# Ajoutez ceci dans votre template là où vous voulez afficher le lien de déconnexion #}
<form action="index.php" method="GET">
    <input type="hidden" name="action" value="logout">
    <button type="submit">Déconnexion</button>
</form>
 <footer>
        Copyright &copy; AccordEnergie 2023
    </footer>
</body>
</html>
