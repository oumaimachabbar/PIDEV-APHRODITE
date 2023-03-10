namespace App\Service;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;

class ProduitStatsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getStats(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.nom, p.quantite, p.prix')
           ->from(Produit::class, 'p');

        $results = $qb->getQuery()->getResult();

        $stats = [
            'quantite' => [
                'max' => 0,
                'min' => 0,
                'average' => 0,
            ],
            'prix' => [
                'max' => 0,
                'min' => 0,
                'average' => 0,
            ],
            'nom' => [
                'most_common' => '',
                'least_common' => '',
            ],
        ];

        $quantiteSum = 0;
        $prixSum = 0;
        $quantiteCount = count($results);
        $prixCount = count($results);
        $nomCounts = [];

        foreach ($results as $result) {
            // Quantite stats
            $quantite = $result['quantite'];
            $quantiteSum += $quantite;

            if ($quantite > $stats['quantite']['max']) {
                $stats['quantite']['max'] = $quantite;
            }

            if ($quantite < $stats['quantite']['min'] || $stats['quantite']['min'] === 0) {
                $stats['quantite']['min'] = $quantite;
            }

            // Prix stats
            $prix = $result['prix'];
            $prixSum += $prix;

            if ($prix > $stats['prix']['max']) {
                $stats['prix']['max'] = $prix;
            }

            if ($prix < $stats['prix']['min'] || $stats['prix']['min'] === 0) {
                $stats['prix']['min'] = $prix;
            }

            // Nom stats
            $nom = $result['nom'];

            if (!isset($nomCounts[$nom])) {
                $nomCounts[$nom] = 1;
            } else {
                $nomCounts[$nom]++;
            }
        }

        // Quantite stats
        $stats['quantite']['average'] = $quantiteSum / $quantiteCount;

        // Prix stats
        $stats['prix']['average'] = $prixSum / $prixCount;

        // Nom stats
        $stats['nom']['most_common'] = array_search(max($nomCounts), $nomCounts);
        $stats['nom']['least_common'] = array_search(min($nomCounts), $nomCounts);

        return $stats;
    }
}
