import json
import random
import os
import datetime
import time

HISTORIQUE_FILE = "historique.json"
CHOIX_POSSIBLES = ["pierre", "feuille", "ciseau"]

def charger_historique():
    if not os.path.exists(HISTORIQUE_FILE):
        return []
    with open(HISTORIQUE_FILE, "r") as f:
        return json.load(f)

def sauvegarder_historique(historique):
    with open(HISTORIQUE_FILE, "w") as f:
        json.dump(historique, f, indent=4)

def afficher_menu():
    print("\n=== MENU PRINCIPAL ===")
    print("1. Nouvelle partie")
    print("2. Consulter l'historique des parties")
    print("3. Consulter les statistiques")
    print("4. Quitter")

def jouer_partie(historique):
    print("\n--- Nouvelle partie ---")
    print("Tapez 'annuler' pour revenir au menu")
    debut = time.time()
    choix_joueur = input("Choisissez (pierre, feuille, ciseau) : ").lower()
    if choix_joueur == "annuler":
        print("Partie annulée, retour au menu.")
        return
    if choix_joueur not in CHOIX_POSSIBLES:
        print("Choix invalide.")
        return

    choix_cpu = random.choice(CHOIX_POSSIBLES)

    def resultat(j, c):
        if j == c:
            return "égalité"
        elif (j == "pierre" and c == "ciseau") or \
             (j == "feuille" and c == "pierre") or \
             (j == "ciseau" and c == "feuille"):
            return "victoire"
        else:
            return "défaite"

    res = resultat(choix_joueur, choix_cpu)
    print(f"Vous : {choix_joueur} | CPU : {choix_cpu} --> Résultat : {res}")

    fin = time.time()
    duree = round(fin - debut, 2)
    historique.append({
        "date": datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "joueur": choix_joueur,
        "cpu": choix_cpu,
        "résultat": res,
        "durée": duree
    })
    sauvegarder_historique(historique)

    while True:
        next_step = input("\n1. Retour au menu\n2. Rejouer\n> ")
        if next_step == "1":
            break
        elif next_step == "2":
            jouer_partie(historique)
            break

def afficher_historique(historique):
    print("\n--- Historique des parties ---")
    if not historique:
        print("Aucune partie enregistrée.")
        return

    par_page = 5
    pages = (len(historique) - 1) // par_page + 1
    page = 1

    while True:
        debut = (page - 1) * par_page
        fin = debut + par_page
        print(f"\nPage {page}/{pages}")
        print("{:<20} {:<10} {:<10} {:<10}".format("Date", "Joueur", "CPU", "Résultat"))
        print("-" * 50)
        for partie in historique[debut:fin]:
            print("{:<20} {:<10} {:<10} {:<10}".format(
                partie['date'], partie['joueur'], partie['cpu'], partie['résultat']
            ))

        if pages == 1:
            break
        print("\nN = Suivante | P = Précédente | M = Menu")
        cmd = input("Choix : ").lower()
        if cmd == "n" and page < pages:
            page += 1
        elif cmd == "p" and page > 1:
            page -= 1
        elif cmd == "m":
            break

def calculer_statistiques(historique):
    print("\n--- Statistiques ---")
    if not historique:
        print("Aucune donnée pour les statistiques.")
        return

    nb_total = len(historique)
    nb_victoires = sum(1 for p in historique if p["résultat"] == "victoire")
    nb_egalites = sum(1 for p in historique if p["résultat"] == "égalité")
    nb_defaites = sum(1 for p in historique if p["résultat"] == "défaite")
    taux_victoire = nb_victoires / nb_total * 100

    # Main la plus gagnante
    main_stats = {m: {"victoire": 0, "jouée": 0} for m in CHOIX_POSSIBLES}
    for p in historique:
        main_stats[p["joueur"]]["jouée"] += 1
        if p["résultat"] == "victoire":
            main_stats[p["joueur"]]["victoire"] += 1

    plus_gagnante = max(main_stats.items(), key=lambda x: x[1]["victoire"])[0]
    temps_total = sum(p.get("durée", 0) for p in historique)

    print(f"Nombre de parties jouées : {nb_total}")
    print(f"Taux de victoire : {taux_victoire:.2f}%")
    print(f"Taux d'égalité : {(nb_egalites / nb_total * 100):.2f}%")
    print(f"Taux de défaite : {(nb_defaites / nb_total * 100):.2f}%")
    print(f"Main la plus gagnante : {plus_gagnante}")

    print("\nTaux de victoire par main :")
    for main, data in main_stats.items():
        if data["jouée"] == 0:
            taux = 0
        else:
            taux = data["victoire"] / data["jouée"] * 100
        print(f"- {main.capitalize()} : {taux:.2f}%")

    print(f"\nTemps total passé à jouer : {temps_total:.2f} secondes")

def main():
    historique = charger_historique()

    while True:
        afficher_menu()
        choix = input("Choisissez une option : ")
        if choix == "1":
            jouer_partie(historique)
        elif choix == "2":
            afficher_historique(historique)
        elif choix == "3":
            calculer_statistiques(historique)
        elif choix == "4":
            print("Merci d'avoir joué ! À bientôt.")
            break
        else:
            print("Option invalide.")

if __name__ == "__main__":
    main()
