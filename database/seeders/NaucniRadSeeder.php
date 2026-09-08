<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NaucniRad;
use App\Models\Oblast;

class NaucniRadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->uvezeniRadovi();
        $this->interniRadovi();
    }

    private function uvezeniRadovi(): void
    {
        $grupa = 1;

        foreach ($this->pravRadovi() as $podaci) {
            $oblasti = $podaci['oblasti'];
            unset($podaci['oblasti']);

            $rad = NaucniRad::updateOrCreate(
                ['naslov' => $podaci['naslov']],
                $podaci + ['grupaId' => $grupa, 'verzija' => 1, 'StatusID' => 3]
            );

            $idOblasti = Oblast::whereIn('naziv', $oblasti)->pluck('oblastId');
            $rad->oblasti()->syncWithoutDetaching($idOblasti);

            $grupa++;
        }
    }

    private function interniRadovi(): void
    {
        if (NaucniRad::whereNull('DOI')->exists()) {
            return;
        }

        $grupa = NaucniRad::max('grupaId') + 1;

        foreach ([1, 1, 2, 2, 4] as $statusId) {
            NaucniRad::factory()->create([
                'DOI'           => null,
                'spoljniAutori' => null,
                'grupaId'       => $grupa,
                'verzija'       => 1,
                'StatusID'      => $statusId,
            ]);

            $grupa++;
        }
    }

    private function pravRadovi(): array
    {
        return [
            [
                'naslov'        => 'Deep learning',
                'DOI'           => '10.1038/nature14539',
                'spoljniAutori' => 'Yann LeCun, Yoshua Bengio, Geoffrey Hinton',
                'godina'        => 2015,
                'kljucneReci'   => 'deep learning, neural networks, representation learning',
                'abstrakt'      => 'Pregledni rad o dubokom učenju i višeslojnim neuronskim mrežama, sa osvrtom na primenu u prepoznavanju govora i slike.',
                'oblasti'       => ['Veštačka inteligencija'],
            ],
            [
                'naslov'        => 'LIBSVM',
                'DOI'           => '10.1145/1961189.1961199',
                'spoljniAutori' => 'Chih-Chung Chang, Chih-Jen Lin',
                'godina'        => 2011,
                'kljucneReci'   => 'support vector machines, classification, regression',
                'abstrakt'      => 'Biblioteka za rad sa metodama potpornih vektora, sa naglaskom na jednostavnu primenu u klasifikaciji i regresiji.',
                'oblasti'       => ['Veštačka inteligencija', 'Informacione Tehnologije'],
            ],
            [
                'naslov'        => 'Fiji: an open-source platform for biological-image analysis',
                'DOI'           => '10.1038/nmeth.2019',
                'spoljniAutori' => 'Johannes Schindelin, Ignacio Arganda-Carreras, Erwin Frise, Verena Kaynig, Mark Longair, Tobias Pietzsch i još 10',
                'godina'        => 2012,
                'kljucneReci'   => 'image processing, biological imaging, open source software',
                'abstrakt'      => 'Prikaz platforme Fiji otvorenog koda namenjene obradi i analizi bioloških slika.',
                'oblasti'       => ['Biologija', 'Informacione Tehnologije'],
            ],
            [
                'naslov'        => 'NIH Image to ImageJ: 25 years of image analysis',
                'DOI'           => '10.1038/nmeth.2089',
                'spoljniAutori' => 'Caroline A. Schneider, Wayne S. Rasband, Kevin W. Eliceiri',
                'godina'        => 2012,
                'kljucneReci'   => 'image analysis, ImageJ, scientific software',
                'abstrakt'      => 'Osvrt na dvadeset pet godina razvoja alata ImageJ za analizu naučnih slika.',
                'oblasti'       => ['Biologija', 'Informacione Tehnologije'],
            ],
            [
                'naslov'        => 'MEGA7: Molecular Evolutionary Genetics Analysis Version 7.0 for Bigger Datasets',
                'DOI'           => '10.1093/molbev/msw054',
                'spoljniAutori' => 'Sudhir Kumar, Glen Stecher, Koichiro Tamura',
                'godina'        => 2016,
                'kljucneReci'   => 'molecular evolution, phylogenetics, large datasets',
                'abstrakt'      => 'Sedma verzija programskog paketa MEGA za molekularnu evolucionu genetsku analizu, prilagođena većim skupovima podataka.',
                'oblasti'       => ['Biologija'],
            ],
            [
                'naslov'        => 'MEGA X: Molecular Evolutionary Genetics Analysis across Computing Platforms',
                'DOI'           => '10.1093/molbev/msy096',
                'spoljniAutori' => 'Sudhir Kumar, Glen Stecher, Michael Li, Christina Knyaz, Koichiro Tamura',
                'godina'        => 2018,
                'kljucneReci'   => 'molecular evolution, phylogenetics, cross-platform software',
                'abstrakt'      => 'Verzija paketa MEGA dostupna na više operativnih sistema, sa alatima za filogenetsku analizu.',
                'oblasti'       => ['Biologija', 'Informacione Tehnologije'],
            ],
            [
                'naslov'        => 'IQ-TREE: A Fast and Effective Stochastic Algorithm for Estimating Maximum-Likelihood Phylogenies',
                'DOI'           => '10.1093/molbev/msu300',
                'spoljniAutori' => 'Lam-Tung Nguyen, Heiko A. Schmidt, Arndt von Haeseler, Bui Quang Minh',
                'godina'        => 2014,
                'kljucneReci'   => 'phylogenetics, stochastic algorithms, maximum likelihood',
                'abstrakt'      => 'Stohastički algoritam za brzu procenu filogenetskih stabala metodom maksimalne verodostojnosti.',
                'oblasti'       => ['Biologija', 'Matematika'],
            ],
            [
                'naslov'        => 'SPAdes: A New Genome Assembly Algorithm and Its Applications to Single-Cell Sequencing',
                'DOI'           => '10.1089/cmb.2012.0021',
                'spoljniAutori' => 'Anton Bankevich, Sergey Nurk, Dmitry Antipov, Alexey A. Gurevich, Mikhail Dvorkin, Alexander S. Kulikov i još 10',
                'godina'        => 2012,
                'kljucneReci'   => 'genome assembly, sequencing, algorithms',
                'abstrakt'      => 'Algoritam za sastavljanje genoma prilagođen sekvenciranju pojedinačnih ćelija.',
                'oblasti'       => ['Biologija', 'Matematika'],
            ],
            [
                'naslov'        => 'SciPy 1.0: fundamental algorithms for scientific computing in Python',
                'DOI'           => '10.1038/s41592-019-0686-2',
                'spoljniAutori' => 'Pauli Virtanen, Ralf Gommers, Travis E. Oliphant, Matt Haberland, Tyler Reddy, David Cournapeau i još 106',
                'godina'        => 2020,
                'kljucneReci'   => 'scientific computing, numerical algorithms, Python',
                'abstrakt'      => 'Opis biblioteke SciPy i njenih osnovnih algoritama za naučno računanje u programskom jeziku Python.',
                'oblasti'       => ['Informacione Tehnologije', 'Matematika'],
            ],
            [
                'naslov'        => 'Clinical features of patients infected with 2019 novel coronavirus in Wuhan, China',
                'DOI'           => '10.1016/s0140-6736(20)30183-5',
                'spoljniAutori' => 'Chaolin Huang, Yeming Wang, Xingwang Li, Lili Ren, Jianping Zhao, Yi Hu i još 23',
                'godina'        => 2020,
                'kljucneReci'   => 'coronavirus, clinical features, epidemiology',
                'abstrakt'      => 'Opis kliničke slike prvih pacijenata zaraženih novim koronavirusom u Vuhanu.',
                'oblasti'       => ['Medicina'],
            ],
            [
                'naslov'        => 'Clinical Characteristics of Coronavirus Disease 2019 in China',
                'DOI'           => '10.1056/nejmoa2002032',
                'spoljniAutori' => 'Wei-jie Guan, Zheng-yi Ni, Yu Hu, Wen-hua Liang, Chun-quan Ou, Jian-xing He i još 31',
                'godina'        => 2020,
                'kljucneReci'   => 'COVID-19, clinical characteristics, patient cohort',
                'abstrakt'      => 'Analiza kliničkih karakteristika obolelih od bolesti COVID-19 na uzorku pacijenata iz Kine.',
                'oblasti'       => ['Medicina'],
            ],
            [
                'naslov'        => 'Clinical course and risk factors for mortality of adult inpatients with COVID-19 in Wuhan, China: a retrospective cohort study',
                'DOI'           => '10.1016/s0140-6736(20)30566-3',
                'spoljniAutori' => 'Fei Zhou, Ting Yu, Ronghui Du, Guohui Fan, Ying Liu, Zhibo Liu i još 13',
                'godina'        => 2020,
                'kljucneReci'   => 'COVID-19, risk factors, mortality',
                'abstrakt'      => 'Retrospektivna kohortna studija toka bolesti i faktora rizika za smrtni ishod kod odraslih pacijenata sa COVID-19.',
                'oblasti'       => ['Medicina'],
            ],
            [
                'naslov'        => 'Quantum Computing in the NISQ era and beyond',
                'DOI'           => '10.22331/q-2018-08-06-79',
                'spoljniAutori' => 'John Preskill',
                'godina'        => 2018,
                'kljucneReci'   => 'quantum computing, qubits, NISQ',
                'abstrakt'      => 'Razmatranje mogućnosti kvantnih računara sa pedeset do sto kubita i pravaca daljeg razvoja.',
                'oblasti'       => ['Fizika', 'Informacione Tehnologije'],
            ],
            [
                'naslov'        => 'Quantum supremacy using a programmable superconducting processor',
                'DOI'           => '10.1038/s41586-019-1666-5',
                'spoljniAutori' => 'Frank Arute, Kunal Arya, Ryan Babbush, Dave Bacon, Joseph C. Bardin, Rami Barends i još 71',
                'godina'        => 2019,
                'kljucneReci'   => 'quantum supremacy, superconducting processor, quantum algorithms',
                'abstrakt'      => 'Demonstracija kvantne nadmoći na programabilnom superprovodnom procesoru.',
                'oblasti'       => ['Fizika'],
            ],
            [
                'naslov'        => 'The ORCA quantum chemistry program package',
                'DOI'           => '10.1063/5.0004608',
                'spoljniAutori' => 'Frank Neese, Frank Wennmohs, Ute Becker, Christoph Riplinger',
                'godina'        => 2020,
                'kljucneReci'   => 'quantum chemistry, numerical methods, scientific software',
                'abstrakt'      => 'Opis programskog paketa ORCA za kvantnohemijska izračunavanja.',
                'oblasti'       => ['Fizika', 'Informacione Tehnologije'],
            ],
            [
                'naslov'        => 'Effect of the damping function in dispersion corrected density functional theory',
                'DOI'           => '10.1002/jcc.21759',
                'spoljniAutori' => 'Stefan Grimme, Stephan Ehrlich, Lars Goerigk',
                'godina'        => 2011,
                'kljucneReci'   => 'density functional theory, dispersion correction, damping function',
                'abstrakt'      => 'Ispitivanje uticaja matematičkog oblika prigušne funkcije u metodama DFT sa korekcijom disperzije.',
                'oblasti'       => ['Fizika', 'Matematika'],
            ],
        ];
    }
}
