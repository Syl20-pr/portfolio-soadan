<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/page.php';

$LANG = 'en';
$PAGE = 'about';
$BASE = '../';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/../includes/header.php';
?>


  <main id="main">
    <section class="page-hero">
      <div class="container">
        <p class="kicker">About</p>
        <h1>A path built between the machine, the rule and the public decision.</h1>
        <p class="lead">My name is Koffi Sylvain SOADAN. I grew up in Lomé, I am learning to write software, and I am
          interested in how a society decides what technology is allowed to do.</p>
      </div>
    </section>

    <nav class="about-nav" aria-label="Page sections">
      <div class="container">
        <a href="#qui-je-suis" data-jump>Who I am</a>
        <a href="#parcours" data-jump>Journey</a>
        <a href="#experiences" data-jump>Experience</a>
        <a href="#ce-qui-me-definit" data-jump>What defines me</a>
        <a href="#vision" data-jump>Vision</a>
        <a href="#valeurs" data-jump>Values</a>
      </div>
    </nav>

    <!-- ============================================================ Qui je suis -->
    <section class="section" id="qui-je-suis" aria-labelledby="identity-title">
      <div class="container identity-grid" data-reveal>
        <figure class="identity-portrait">
          <img src="../assets/images/photo1.jpeg" alt="Portrait of SOADAN Koffi Sylvain" loading="lazy">
          <figcaption>SOADAN Koffi Sylvain · Lomé, Togo</figcaption>
        </figure>

        <div class="identity-intro">
          <p class="eyebrow">Who I am</p>
          <h2 id="identity-title" class="section-title">How a system works, and how a society decides to govern
            it.</h2>

          <p class="lead-strong">I try to understand two things at the same time: how a technical system works, and how
            a society decides what that system is allowed to do.</p>

          <p>Computing attracted me through objects before arguments. The first program that runs, the first database
            modelled properly, the first time a network answers: that is where I understood I wanted to build rather
            than merely comment. Since then I have been pursuing a degree in <strong>software engineering and
              information systems at IAI-Togo</strong>, where I study algorithms, architecture, databases and
            networks.</p>

          <p>One question came back quickly: who decides what a system may do with a citizen's data? That question led
            me to <strong>public law at the University of Lomé</strong>, then to <strong>political science at the
              University of Kara</strong>. I do not follow these three programmes in parallel out of a taste for
            collecting degrees. I follow them because AI governance, data protection and cybersecurity cannot be
            handled well by an engineer alone, a lawyer alone, or a political scientist alone.</p>

          <p>What interests me, in one word, is <strong>digital diplomacy</strong>: understanding how African states
            negotiate, adopt and enforce shared rules on technologies they do not always host. It is a field where one
            must speak both network protocol and negotiating protocol, and that is exactly where I want to build my
            place.</p>

          <p>I document this work in public without inflating its progress: specifications, prototypes, analysis notes
            and code. This portfolio is a progress log as much as a showcase.</p>
        </div>
      </div>
    </section>

    <!-- =============================================================== Parcours -->
    <section class="section section-soft" id="parcours" aria-labelledby="journey-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">My journey</p>
          <h2 id="journey-title" class="section-title">The steps, in the order they happened.</h2>
          <p class="section-lead">A path often starts with an accident. These are the moments that mattered, and what I
            took from them.</p>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2022</p>
          <h3>Togolese Red Cross</h3>
          <p class="stage-place">Volunteer and first aider</p>
          <p class="stage-text">The first commitment that taught me a skill is only worth something when it serves
            someone. First-aid training, presence in the field, discipline of the gestures.</p>
          <ul class="stage-skills">
            <li>First aid</li>
            <li>Teamwork</li>
            <li>Civic engagement</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2023 to 2025</p>
          <h3>Lycée Agoè-Centre</h3>
          <p class="stage-place">Student leader, science, health and environment clubs</p>
          <p class="stage-text">Speaking on behalf of students, organising, persuading. That is where I first touched
            Arduino and robotics, and where programming moved from coursework to project work.</p>
          <ul class="stage-skills">
            <li>Arduino</li>
            <li>Robotics</li>
            <li>Public speaking</li>
            <li>Event organisation</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2024 to 2025</p>
          <h3>Scientific Baccalaureate, C4 series</h3>
          <p class="stage-place">Secondary education, Togo</p>
          <p class="stage-text">A demanding scientific track that gave me the logical basis of engineering, and the
            habit of working a problem until it gives way.</p>
          <ul class="stage-skills">
            <li>Mathematics</li>
            <li>Scientific method</li>
            <li>Rigour</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 to present</p>
          <h3>Software engineering and information systems</h3>
          <p class="stage-place">African Institute of Computer Science (IAI-Togo)</p>
          <p class="stage-text">Second year. Software development, programming, relational databases, networks and
            digital technologies. This is the technical column of everything else, and the part of my path that demands
            the most daily work.</p>
          <ul class="stage-skills">
            <li>Python, Java, C</li>
            <li>Django, React</li>
            <li>SQL</li>
            <li>TCP/IP networking</li>
            <li>Modelling</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 to present</p>
          <h3>Teaching programming, AI and cybersecurity</h3>
          <p class="stage-place">iBase, Togo</p>
          <p class="stage-text">Supporting young learners forced me to clarify what I thought I knew. A concept is only
            really understood at the moment you have to explain it to someone with no reason to take your word for it.
          </p>
          <ul class="stage-skills">
            <li>Teaching</li>
            <li>Technical communication</li>
            <li>Artificial intelligence</li>
            <li>Cybersecurity</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 to present</p>
          <h3>Youth health and national representation</h3>
          <p class="stage-place">AIMES-Afrique / Health Corps / SOS Docteur TV Togo</p>
          <p class="stage-text">Representation and coordination around youth health initiatives, alongside an online
            volunteering assignment for the United Nations on neglected tropical diseases in Guinea-Conakry.</p>
          <ul class="stage-skills">
            <li>Advocacy</li>
            <li>Coordination</li>
            <li>Communication</li>
            <li>Public health</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 to present</p>
          <h3>Compassion International Togo</h3>
          <p class="stage-place">Youth leadership and community advocacy</p>
          <p class="stage-text">Community leadership, environmental protection, and above all a lesson about youth
            participation in local decisions: a policy changes nothing if nobody feels concerned by it.</p>
          <ul class="stage-skills">
            <li>Leadership</li>
            <li>Local decision-making</li>
            <li>Environment</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2026 to present</p>
          <h3>Public law</h3>
          <p class="stage-place">University of Lomé · Faculty of Law</p>
          <p class="stage-text">Where I learn to read a normative text, to separate a rule from an intention, and to
            understand how a state organises its own constraint. This is the bridge between my technical work and the
            idea of governance.</p>
          <ul class="stage-skills">
            <li>Constitutional law</li>
            <li>Administrative law</li>
            <li>Public international law</li>
            <li>Civil liberties</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2026 to present</p>
          <h3>Political science</h3>
          <p class="stage-place">University of Kara</p>
          <p class="stage-text">Institutions, power, public policy, international relations and multilateral
            organisations. This is where my curiosity about treaties and negotiations became a working subject.</p>
          <ul class="stage-skills">
            <li>International relations</li>
            <li>Public policy analysis</li>
            <li>African geopolitics</li>
            <li>Multilateral organisations</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2026 to present</p>
          <h3>International cooperation and digital diplomacy</h3>
          <p class="stage-place">MoNaJeL Togo · IAI-Togo student bureau · student network supported by the U.S.
            Embassy</p>
          <p class="stage-text">General advisor at MoNaJeL on youth and international cooperation, general advisor at
            the IAI-Togo student bureau, student delegate in a network supported by the U.S. Embassy. Three different
            places for the same question: how an African institution connects to the wider world.</p>
          <ul class="stage-skills">
            <li>Digital diplomacy</li>
            <li>International cooperation</li>
            <li>Network facilitation</li>
            <li>Institutional governance</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- ============================================================ Expériences -->
    <section class="section" id="experiences" aria-labelledby="exp-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">My experience</p>
          <h2 id="exp-title" class="section-title">What I have actually done.</h2>
          <p class="section-lead">Each role is stated as it was held, without inflated responsibility. Detailed entries
            open on click.</p>
        </div>

        <div class="grid-2">
          <article class="exp-card" data-reveal>
            <h3>Programming, AI and cybersecurity instructor</h3>
            <p class="exp-org">iBase · Togo · current</p>
            <p class="exp-summary">Mentoring young learners in programming, artificial intelligence and
              cybersecurity.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-ibase">See details</button>
            <div class="exp-detail" id="exp-ibase" hidden>
              <p>The work is about making often abstract ideas accessible. I adapt the pace, review practical exercises
                and introduce the basics of information security in concrete terms.</p>
              <ul>
                <li>Programming workshops</li>
                <li>Introduction to artificial intelligence</li>
                <li>Cybersecurity fundamentals</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>General advisor</h3>
            <p class="exp-org">MoNaJeL Togo · 2026 to present</p>
            <p class="exp-summary">Supporting youth initiatives, international cooperation, leadership and
              mobilisation.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-monajel">See details</button>
            <div class="exp-detail" id="exp-monajel" hidden>
              <p>The National Movement of Young Leaders for International Cooperation works on the link between youth
                and international openness. I support the preparation of activities and the mobilisation of young people
                around these questions.</p>
              <ul>
                <li>Support for youth initiatives</li>
                <li>Contribution to international cooperation exchanges</li>
                <li>Facilitation and mobilisation</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>National representative</h3>
            <p class="exp-org">AIMES-Afrique / Health Corps / SOS Docteur TV Togo · 2025 to present</p>
            <p class="exp-summary">Representation and coordination around youth health initiatives.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-aimes">See details</button>
            <div class="exp-detail" id="exp-aimes" hidden>
              <p>Awareness on youth health and representation of these initiatives to other actors, in connection with
                SOS Docteur TV activities.</p>
              <ul>
                <li>Representation with partners</li>
                <li>Coordination of awareness campaigns</li>
                <li>Public health communication</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>General advisor, student bureau</h3>
            <p class="exp-org">IAI-Togo · 2026 to present</p>
            <p class="exp-summary">Promoting student projects, programming and technical workshops.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-iai">See details</button>
            <div class="exp-detail" id="exp-iai" hidden>
              <p>Within the student bureau I push collective projects and technical workshops, so that programming
                remains a practice rather than merely a subject to revise.</p>
              <ul>
                <li>Promoting student projects</li>
                <li>Organising technical workshops</li>
                <li>Student life facilitation</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Student delegate and group administrator</h3>
            <p class="exp-org">Student network supported by the U.S. Embassy · 2026 to present</p>
            <p class="exp-summary">Coordinating information and running digital activities.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-us">See details</button>
            <div class="exp-detail" id="exp-us" hidden>
              <p>A network of students exchanging information and taking part in digital activities. I handle the flow
                of information and the organisation of some activities.</p>
              <ul>
                <li>Information coordination</li>
                <li>Digital activity facilitation</li>
                <li>Communication between students</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Student leader and science, health, environment clubs</h3>
            <p class="exp-org">Lycée Agoè-Centre · Togo · 2023 to 2025</p>
            <p class="exp-summary">Speaking for students, Arduino and robotics activities, awareness work.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-lycee">See details</button>
            <div class="exp-detail" id="exp-lycee" hidden>
              <p>A role that taught me to represent a group, defend a proposal before a school leadership, and organise
                activities with limited resources.</p>
              <ul>
                <li>Student representation</li>
                <li>Arduino and robotics workshops</li>
                <li>Health and environment awareness</li>
                <li>Tree planting</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Volunteer and first aider</h3>
            <p class="exp-org">Togolese Red Cross Society · 2022 to present</p>
            <p class="exp-summary">Volunteering and first-aid training.</p>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Online volunteer</h3>
            <p class="exp-org">UN Volunteers · United Nations</p>
            <p class="exp-summary">Communication and awareness on neglected tropical diseases.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-unv">See details</button>
            <div class="exp-detail" id="exp-unv" hidden>
              <p>An online volunteering assignment on communication and awareness around neglected tropical diseases
                in Guinea-Conakry, with a focus on rural communities in West Africa.</p>
              <ul>
                <li>Communication</li>
                <li>Health awareness</li>
                <li>West African context</li>
              </ul>
            </div>
          </article>
        </div>

        <p class="mt-4 mb-0"><a class="btn btn-secondary" href="experience.php">See the full chronology</a></p>
      </div>
    </section>

    <!-- ==================================================== What defines me -->
    <section class="section section-soft" id="ce-qui-me-definit" aria-labelledby="pillars-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">What defines me</p>
          <h2 id="pillars-title" class="section-title">Five dimensions, one profile.</h2>
          <p class="section-lead">My profile is not scattered: it rests on five axes that answer each other.</p>
        </div>

        <div class="pillar-grid" data-reveal>
          <article class="pillar">
            <span class="pillar-label">Technology</span>
            <p>Software development, artificial intelligence, cybersecurity, information systems.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Governance</span>
            <p>Public law, public policy, digital governance, AI governance.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Diplomacy</span>
            <p>International relations, digital diplomacy, international cooperation.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Engagement</span>
            <p>Youth, volunteering, community development and civic engagement.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Africa</span>
            <p>Innovation, digital transformation and the continent's development.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ================================================================= Vision -->
    <section class="section" id="vision" aria-labelledby="vision-title">
      <div class="container grid-2" data-reveal>
        <div>
          <p class="eyebrow">My vision</p>
          <h2 id="vision-title" class="section-title">Technology is only half of the problem.</h2>
        </div>
        <div class="prose">
          <blockquote class="pull-quote">An infrastructure without a rule remains fragile. A rule without an
            infrastructure remains theoretical.</blockquote>

          <p>My ambition fits in one sentence: to build a career at the intersection of <strong>technology, law,
              politics, diplomacy and African development</strong>. I am not trying to choose between these worlds,
            because the problems I want to work on refuse that separation.</p>

          <p>When an African state wants to host its citizens' health data, there is not a single technical obstacle.
            There is a question of norms, a question of budget, a question of sovereignty and a question of negotiating
            with partners. A country without engineers able to design the system negotiates badly. A country with the
            engineers but not the lawyers deploys it badly. And a country with both but no negotiating skill has it
            imposed on it.</p>

          <p>That is the chain I want to understand, from protocol to treaty. My conviction is that African technology
            is not played out only in server rooms: it is also played out in committees, conventions and resolutions.
            The Malabo Convention, Agenda 2063 and the continental AI strategy are not decorative texts. They are
            instruments, and they need people able to translate them into code.</p>

          <p>In the long term I want to contribute to AI governance frameworks, cybersecurity policy and digital public
            infrastructure designed for the continent's realities, and defensible in the forums where Africa speaks to
            the world. Three capabilities seem necessary: solid technical expertise, legal and normative capacity, and
            the ability to work multilaterally. I am building them one by one, and saying so plainly.</p>

          <p class="note"><strong>Transparency:</strong> the information in this portfolio reflects only training,
            engagements and projects that are genuinely underway. Where information is not available, it is flagged as
            such rather than invented. None of my projects is presented as deployed.</p>
        </div>
      </div>
    </section>

    <!-- =============================================================== Values -->
    <section class="section section-soft" id="valeurs" aria-labelledby="values-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">My values</p>
          <h2 id="values-title" class="section-title">What I try not to negotiate.</h2>
          <p class="section-lead">Principles learned while working, not a list chosen for comfort.</p>
        </div>

        <ul class="value-list" data-reveal>
          <li>
            <span class="value-key">Integrity</span>
            <span class="value-text">Not inflating a result to make it look more advanced than it is. Saying when a
              project is a concept and not a product.</span>
          </li>
          <li>
            <span class="value-key">Service</span>
            <span class="value-text">A skill that serves nobody is just an ornament. The Red Cross taught me that
              before anything else.</span>
          </li>
          <li>
            <span class="value-key">Rigour</span>
            <span class="value-text">Code is re-read, a norm is cited, a figure is checked. Detail is not a
              nicety.</span>
          </li>
          <li>
            <span class="value-key">Learning</span>
            <span class="value-text">I would rather admit what I do not yet know and work on it than talk about it as if
              I had mastered it.</span>
          </li>
          <li>
            <span class="value-key">Cooperation</span>
            <span class="value-text">The problems I want to address exceed one individual and often a single country.
              Working with others is not an effort, it is the method.</span>
          </li>
          <li>
            <span class="value-key">Responsibility</span>
            <span class="value-text">A system you build commits those who use it. Designing is already deciding for
              someone else.</span>
          </li>
          <li>
            <span class="value-key">African grounding</span>
            <span class="value-text">Starting from local realities, languages, institutions and constraints, rather
              than importing a finished model.</span>
          </li>
          <li>
            <span class="value-key">Leadership</span>
            <span class="value-text">Speaking for a group, owning the decision, and stepping back when the time
              comes.</span>
          </li>
          <li>
            <span class="value-key">Impact</span>
            <span class="value-text">I judge a project by what it changes in practice, not by how many people it
              impresses.</span>
          </li>
        </ul>
      </div>
    </section>

    <!-- ============================================================== Next -->
    <section class="section" aria-labelledby="refs-title">
      <div class="container grid-2" data-reveal>
        <div>
          <p class="eyebrow">Going further</p>
          <h2 id="refs-title" class="section-title">Verify rather than believe.</h2>
          <p class="section-lead">The detail of my path is documented page by page, with each project's stage stated
            honestly.</p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="projects.php">See my projects</a>
            <a class="btn btn-secondary" href="contact.php">Get in touch</a>
          </div>
        </div>
        <div>
          <ul class="doc-list">
            <li><span class="doc-name">Education</span><a class="link-inline" href="education.php">Three programmes in
                parallel</a></li>
            <li><span class="doc-name">Experience</span><a class="link-inline" href="experience.php">Roles, mandates
                and engagements</a></li>
            <li><span class="doc-name">Expertise</span><a class="link-inline" href="expertise.php">Skills and honest
                levels</a></li>
            <li><span class="doc-name">Leadership</span><a class="link-inline" href="leadership.php">Volunteering and
                civic engagement</a></li>
          </ul>
        </div>
      </div>
    </section>

  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
