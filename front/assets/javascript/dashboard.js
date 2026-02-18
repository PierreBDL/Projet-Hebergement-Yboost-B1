// =========================
// Déconnexion
// =========================
function deconnexion() {
    window.location.href = "./login.php";
}

// =========================
// Modal "Nouveau contact"
// =========================
const dialog = document.getElementById("favDialog");
const showButton = document.getElementById("showDialog");
const cancelBtn = document.getElementById("cancelBtn");
const inputContact = document.getElementById("nomNouveauContact");

showButton?.addEventListener("click", () => {
    dialog.showModal();
    inputContact.focus();
});

cancelBtn?.addEventListener("click", (e) => {
    e.preventDefault();
    dialog.close();
    inputContact.value = "";
});

// Fermer en cliquant hors modale
dialog?.addEventListener("click", (e) => {
    if (e.target === dialog) {
        dialog.close();
    }
});

// =========================
// Auto-scroll messages
// =========================
const messagesBox = document.querySelector(".messages");
if (messagesBox) {
    messagesBox.scrollTop = messagesBox.scrollHeight;
}

// =========================
// Auto-resize textarea
// =========================
const textarea = document.getElementById("messageRediger");

textarea?.addEventListener("input", () => {
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + "px";
});


// =========================
// Envoie de l'invitation
// =========================

document.getElementById("formInvitation").addEventListener("submit", function (e) {
    e.preventDefault();

    const pseudo = document.getElementById("nomNouveauContact").value.trim();

    if (pseudo === "") {
        Swal.fire({
            icon: "warning",
            title: "Champ vide",
            text: "Veuillez entrer un pseudo"
        });
        return;
    }

    fetch("./ajax/invitation.inc.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            pseudoDestinataire: pseudo
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire("Invitation envoyée", data.message, "success");
            dialog.close();
        } else {
            Swal.fire("Erreur", data.error, "error");
        }
    })
    .catch(err => console.error(err));
});


// =========================
// Menu pièces jointes
// =========================

const btnPieceJointe = document.getElementById("piece-jointe");
const menuPieceJointe = document.querySelector(".piece-jointe-menu");

btnPieceJointe.addEventListener("click", () => {
    menuPieceJointe.classList.toggle("piece-jointe-menu-ouvert");
});


// =========================
// Détecter le changement sur les inputs file
// =========================

document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const btn = document.getElementById('piece-jointe');
            btn.style.backgroundColor = '#4caf50';

            Swal.fire({
                icon: 'info',
                title: 'Fichier sélectionné',
                text: this.files[0].name,
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
});