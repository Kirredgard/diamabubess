(function(){
  var menu = document.querySelector('.menu');
  var body = document.body;
  var backdrop = document.querySelector('.nav-backdrop');
  if(!menu) return;

  function openMenu(){
    body.classList.add('nav-open');
    menu.classList.add('open');
    menu.setAttribute('aria-expanded','true');
  }
  function closeMenu(){
    body.classList.remove('nav-open');
    menu.classList.remove('open');
    menu.setAttribute('aria-expanded','false');
  }
  menu.addEventListener('click', function(){
    if(body.classList.contains('nav-open')){ closeMenu(); } else { openMenu(); }
  });
  if(backdrop){ backdrop.addEventListener('click', closeMenu); }
  document.querySelectorAll('nav a').forEach(function(a){
    a.addEventListener('click', closeMenu);
  });
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeMenu();
  });
})();

/* Active navigation: robust on local files, subfolders and web hosting. */
(function(){
  var current = (window.location.pathname || '').split('/').filter(Boolean).pop() || '';
  current = current.toLowerCase();
  if (current === 'index.html' || current === 'index') current = '';
  if (/\.html?$/.test(current)) current = current.replace(/\.html?$/, '');

  document.querySelectorAll('.nav-panel nav a').forEach(function(link){
    var href = (link.getAttribute('href') || '').split('#')[0].split('?')[0].split('/').pop().toLowerCase();
    if (/\.html?$/.test(href)) href = href.replace(/\.html?$/, '');
    if (href === 'index') href = '';
    if (!href) {
      link.classList.toggle('active', current === '');
      if (current === '') link.setAttribute('aria-current','page');
      else link.removeAttribute('aria-current');
      return;
    }
    var isActive = href === current;
    link.classList.toggle('active', isActive);
    if (isActive) link.setAttribute('aria-current','page');
    else link.removeAttribute('aria-current');
  });
})();

/* ===== Assistance flottante — Sina : assistante du site ===== */
(function(){
  if(document.querySelector('.sina-assist')) return;
  var root=document.createElement('div'); root.className='sina-assist';
  var avatar='<img class="sina-woman sina-photo" src="assets/sina.png" alt="Sina, assistante de Diama Bu Bess">';
  root.innerHTML=`
    <div class="sina-panel" id="sina-assistance-panel" role="dialog" aria-label="Discussion avec Sina">
      <div class="sina-head"><span class="sina-head-avatar">${avatar}</span><span class="sina-head-copy"><strong>Sina</strong><span>Assistante Diama Bu Bess · En ligne</span></span><button class="sina-close" type="button" aria-label="Fermer">×</button></div>
      <div class="sina-chat" aria-live="polite" aria-label="Conversation avec Sina"><div class="sina-msg sina-msg-bot"><span class="sina-mini">${avatar}</span><div>Bonjour 👋 Je suis <strong>Sina</strong>. Je connais le contenu de ce site et peux répondre précisément sur Diama Bu Bess : le projet, la vision, Abdourahmane Fall, les projets, les 12 axes détaillés, l’Institut de Diama, la gouvernance, la culture, la diaspora, les contacts et l’adhésion.</div></div></div>
      <div class="sina-suggestions"><button type="button" data-q="Quelle est la vision de Diama Bu Bess ?">La vision</button><button type="button" data-q="Qui est Abdourahmane Fall ?">Le concepteur</button><button type="button" data-q="Quels sont tous les grands axes du projet ?">Les priorités</button><button type="button" data-q="Comment rejoindre Diama Bu Bess ?">Nous rejoindre</button></div>
      <form class="sina-form"><input type="text" name="message" placeholder="Posez n’importe quelle question sur le site…" autocomplete="off" aria-label="Votre question"/><button type="submit" aria-label="Envoyer">➤</button></form>
    </div>
    <button class="sina-button" type="button" aria-expanded="false" aria-controls="sina-assistance-panel"><span class="sina-avatar">${avatar}<i class="sina-dot"></i></span><span class="sina-label"><strong>Sina</strong><span>Assistance</span></span></button>`;
  document.body.appendChild(root);
  var button=root.querySelector('.sina-button'),close=root.querySelector('.sina-close'),chat=root.querySelector('.sina-chat'),form=root.querySelector('.sina-form'),input=form.querySelector('input');

  var knowledge=[
    {keys:['vision','quelle est la vision','votre vision'],text:'La vision de Diama Bu Bess est de construire une Diama moderne, solidaire, prospère et tournée vers l’avenir. Le projet place au cœur de l’action la proximité, la responsabilité, l’écoute, l’efficacité et le rassemblement des forces vives de la commune.',link:'vision',label:'Voir Vision'},
    {keys:['fil conducteur','objectif général','objectif du projet','projet pour diama'],text:'Le fil conducteur est de rassembler les forces vives de la commune et de transformer les potentialités de Diama en opportunités concrètes pour les habitants.',link:'/',label:'Voir le projet'},
    {keys:['projet','diama bu bess','mouvement','c est quoi diama bu bess'],text:'Diama Bu Bess est un projet local centré sur le développement de Diama. Il veut mettre les ressources, les talents et la jeunesse de la commune au service de tous, rassembler les forces vives et placer la proximité, l’écoute et l’efficacité au cœur de l’action.',link:'mouvement',label:'Voir Le Projet'},
    {keys:['abdourahmane fall','abdourahmane fall','qui est abdourahmane','concepteur'],text:'Abdourahmane FALL est le concepteur de Diama Bu Bess. Son parcours politique s’est construit au sein d’Awalé. Il a ensuite inscrit son engagement dans la dynamique nationale liée à KIIRAAY. Né et ayant grandi à Diama, il met en avant un parcours construit dans l’effort, l’éducation, l’entrepreneuriat, la foi et la persévérance.',link:'mouvement',label:'Découvrir Le Projet'},
    {keys:['awale','awalé','parti'],text:'Le parcours politique d’Abdourahmane Fall s’est construit au sein du parti Awalé, dirigé par Dr Abdourahmane Diouf. En 2024, Awalé a rejoint la dynamique de la Coalition Diomaye Président.',link:'mouvement',label:'Voir le parcours'},
    {keys:['kiiraay','patriotes républicains','patriotes republicains','cadre national'],text:'KIIRAAY – Les Patriotes Républicains constitue le cadre politique national mentionné dans la présentation. Diama Bu Bess reste un projet local : son rôle est de porter un projet centré sur les réalités de la commune et de contribuer localement à une dynamique de transformation.',link:'mouvement',label:'Voir le positionnement'},
    {keys:['principes','valeurs','intégrité','integrite','proximité','proximite','équité','equite','jeunesse','travail entrepreneuriat','solidarité','solidarite','unité','unite'],text:'Les principes de Diama Bu Bess sont : intégrité, proximité, équité, jeunesse, travail & entrepreneuriat, solidarité et unité. La présentation insiste aussi sur l’intérêt général, l’éthique, la transparence et le rassemblement.',link:'vision',label:'Voir les principes'},
    {keys:['jeunesse','jeunes','entrepreneuriat','emploi','centre communal'],text:'Pour la jeunesse, le projet prévoit un centre communal d’appui à l’entrepreneuriat, des formations techniques, managériales et entrepreneuriales, l’accompagnement des projets de l’idée à la pérennisation, l’accès aux opportunités et financements, ainsi que le développement des métiers liés à l’agriculture, au numérique et aux services.',link:'vision',label:'Voir Jeunesse & entrepreneuriat'},
    {keys:['femmes','femme','autonomie économique','groupements de femmes'],text:'Pour les femmes, le projet prévoit l’accompagnement des groupements, la formation professionnelle, le renforcement de l’autonomie économique, le soutien aux activités génératrices de revenus, la transformation et la commercialisation des produits locaux et un meilleur accès à l’information et aux mécanismes de financement.',link:'vision',label:'Voir Femmes & autonomie'},
    {keys:['agriculture','agriculteurs','producteurs','terres','économie locale'],text:'Le projet agricole vise à améliorer l’accès aux équipements et les conditions de production, accompagner les producteurs et les jeunes, développer l’élevage, valoriser et transformer localement les produits, organiser les zones agricoles et pastorales, sécuriser les points d’eau et les couloirs de transhumance et créer un dialogue permanent entre agriculteurs et éleveurs.',link:'vision',label:'Voir Agriculture & économie locale'},
    {keys:['élevage','elevage','éleveurs','eleveurs','transhumance'],text:'Pour l’élevage, le projet prévoit le développement des activités connexes, des points d’eau partagés, des couloirs de transhumance sécurisés, une meilleure organisation de l’espace rural et des cadres permanents de dialogue entre agriculteurs et éleveurs.',link:'vision',label:'Voir Agriculture & élevage'},
    {keys:['tourisme rural','tourisme écologique','tourisme ecologique','territoire rural'],text:'Le tourisme rural et écologique est envisagé comme un levier complémentaire de développement, en valorisant le territoire de Diama tout en respectant son environnement.',link:'vision',label:'Voir le projet'},
    {keys:['éducation','education','école','ecole','élèves','eleves','matériel pédagogique','materiel pedagogique'],text:'En éducation, le projet prévoit d’améliorer les infrastructures scolaires et l’accès au matériel pédagogique, de renforcer l’accompagnement des élèves et des jeunes et de développer la formation professionnelle comme outil d’insertion.',link:'vision',label:'Voir Éducation'},
    {keys:['eau potable','eau','assainissement','déchets','dechets','propreté','proprete','éclairage public','eclairage public'],text:'Pour l’eau et le cadre de vie, le projet prévoit de renforcer l’accès à l’eau potable, l’assainissement, la gestion des déchets, la propreté des espaces publics, l’éclairage public et l’aménagement des quartiers et villages.',link:'vision',label:'Voir Eau & cadre de vie'},
    {keys:['santé','sante','soins','structures sanitaires','prévention','prevention'],text:'Pour la santé, le projet prévoit de renforcer les structures sanitaires, développer la sensibilisation et la prévention et améliorer l’accès aux soins. La finalité annoncée est une Diama plus propre, plus saine et plus agréable à vivre.',link:'vision',label:'Voir Santé & cadre de vie'},
    {keys:['routes','route','pistes','mobilité','mobilite','espaces publics'],text:'Pour les infrastructures et la mobilité, le projet prévoit d’améliorer les routes et les pistes, faciliter les déplacements entre villages et quartiers et aménager des espaces publics et infrastructures utiles aux activités économiques et sociales.',link:'vision',label:'Voir Infrastructures & mobilité'},
    {keys:['diaspora','ressortissants','diam​ois de l’extérieur','diamoi','extérieur','exterieur'],text:'Pour la diaspora, le projet prévoit un conseil des ressortissants et de la diaspora, un canal d’information régulier avec les Diamois de l’extérieur, la facilitation de certaines démarches à distance, l’orientation des investissements vers des projets structurants, un rassemblement annuel et la transmission d’expertise.',link:'vision',label:'Voir Diaspora'},
    {keys:['gouvernance','transparence','participation','proximité','proximite','efficacité','efficacite','citoyens'],text:'La nouvelle gouvernance locale repose sur quatre engagements : être plus proche, plus transparente, plus participative et plus efficace. Les citoyens doivent être entendus et accompagnés ; la population doit pouvoir comprendre l’utilisation des moyens communaux ; les acteurs locaux doivent pouvoir contribuer ; et les projets doivent partir des besoins réels et être suivis jusqu’à leur réalisation.',link:'vision',label:'Voir la gouvernance'},
    {keys:['institut de diama','institut','daaras','dara','coran','tafsir','mémorisation','memorisation'],text:'L’Institut de Diama est présenté comme un projet phare : une école coranique moderne, un centre de mémorisation et d’interprétation du Coran (Tafsir), avec un pôle de sciences et techniques. L’objectif est de faire dialoguer savoir religieux et sciences modernes.',link:'vision',label:'Voir l’Institut de Diama'},
    {keys:['sciences','mathématiques','mathematiques','physique','chimie','biologie','informatique','programmation'],text:'L’Institut de Diama doit intégrer les mathématiques, la physique, la chimie, la biologie, l’informatique et la programmation au cursus, en complément des enseignements religieux.',link:'vision',label:'Voir l’Institut de Diama'},
    {keys:['intelligence artificielle','ia','robotique','énergies renouvelables','energies renouvelables'],text:'Le projet d’Institut prévoit une ouverture vers l’intelligence artificielle, la robotique et les énergies renouvelables, ainsi qu’un accompagnement destiné à développer la réflexion, l’autonomie, l’esprit critique et l’innovation.',link:'vision',label:'Voir le projet phare'},
    {keys:['culture','identité','identite','festival','artisans','conteurs','musiciens','danseurs'],text:'Le projet culturel prévoit un centre culturel communal, des ateliers, concours, résidences artistiques et formations, un festival annuel mettant en valeur les talents locaux et un tourisme culturel et écologique respectueux de l’environnement et de l’authenticité de Diama.',link:'vision',label:'Voir Culture, identité & tourisme'},
    {keys:['feuille de route','projet en une phrase','résumé','resume'],text:'Le projet en une phrase : faire de Diama une commune où l’éducation, la jeunesse, l’agriculture, l’entrepreneuriat, les femmes, la santé, les infrastructures, la culture et la diaspora contribuent ensemble au développement local.',link:'vision',label:'Voir la feuille de route'},
    {keys:['rejoindre','adhérer','adherer','adhésion','adhesion','participer','inscription'],text:'Pour rejoindre la dynamique, utilisez la page « Nous rejoindre ». Vous pouvez aussi passer par la page Contacts pour prendre contact avec l’équipe.',link:'adhesion',label:'Nous rejoindre'},
    {keys:['contact','téléphone','telephone','numéro','numero','+221 77 587 80 04 - +221 77 630 40 89 - +221 77 650 44 80'],text:'Le numéro affiché sur la présentation de Diama Bu Bess est le +221 77 587 80 04 - +221 77 630 40 89 - +221 77 650 44 80. Pour un échange, vous pouvez aussi utiliser la page Contacts du site.',link:'contact',label:'Ouvrir Contacts'}
  ];

  function norm(s){return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9\s]/g,' ').replace(/\s+/g,' ').trim();}
  function tokens(s){return norm(s).split(' ').filter(function(x){return x.length>2;});}
  function addMessage(text,bot,link,label){
    var row=document.createElement('div');
    row.className='sina-msg '+(bot?'sina-msg-bot':'sina-msg-user');
    var bubble=document.createElement('div');
    bubble.className='sina-bubble';
    bubble.textContent=text;
    if(bot){
      var mini=document.createElement('span');
      mini.className='sina-mini';
      mini.innerHTML=avatar;
      row.appendChild(mini);
      row.appendChild(bubble);
    } else {
      row.appendChild(bubble);
    }
    if(bot && link){
      var br=document.createElement('br');
      var a=document.createElement('a');
      a.className='sina-answer-link';
      a.href=link;
      a.textContent=(label || 'Voir la page')+' →';
      bubble.appendChild(br);
      bubble.appendChild(a);
    }
    chat.appendChild(row);
    chat.scrollTop=chat.scrollHeight;
  }

  function answer(q){
    var t=norm(q), qt=tokens(q), best=null, bestScore=0;
    knowledge.forEach(function(item){
      var score=0;
      item.keys.forEach(function(k){
        var nk=norm(k);
        if(t.indexOf(nk)!==-1) score += nk.length>=8 ? 8 : 5;
        var kt=tokens(k), hit=kt.filter(function(x){return qt.indexOf(x)!==-1;}).length;
        if(hit) score += hit*2;
      });
      if(t.indexOf('qui est')!==-1 && item.keys.some(function(k){return norm(k).indexOf('abdourahmane')!==-1 || norm(k).indexOf('concepteur')!==-1;})) score+=3;
      if((t.indexOf('comment')!==-1 || t.indexOf('rejoindre')!==-1) && item.keys.some(function(k){return ['rejoindre','adhesion','adhérer','participer'].indexOf(norm(k))!==-1;})) score+=5;
      if(score>bestScore){bestScore=score;best=item;}
    });
    if(!best || bestScore<4){
      addMessage('Je n’ai pas trouvé une réponse suffisamment précise dans le contenu du site. Reformulez votre question avec un thème précis (par exemple : jeunesse, agriculture, Institut de Diama, diaspora, gouvernance, Abdourahmane Fall, KIIRAAY ou nous rejoindre) et je chercherai dans les informations disponibles.',true);
      return;
    }
    addMessage(best.text,true,best.link,best.label);
  }

  function openSina(){root.classList.add('open');button.setAttribute('aria-expanded','true');setTimeout(function(){input.focus();},80)}
  function closeSina(){root.classList.remove('open');button.setAttribute('aria-expanded','false')}
  button.addEventListener('click',function(){root.classList.contains('open')?closeSina():openSina()});
  close.addEventListener('click',closeSina);
  root.querySelectorAll('.sina-suggestions button').forEach(function(b){b.addEventListener('click',function(){var q=b.getAttribute('data-q');addMessage(q,false);setTimeout(function(){answer(q)},150)})});
  form.addEventListener('submit',function(e){e.preventDefault();var v=input.value.trim();if(!v)return;addMessage(v,false);input.value='';setTimeout(function(){answer(v)},180)});
  document.addEventListener('click',function(e){if(!root.contains(e.target))closeSina()});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')closeSina()});
})();

/* Galerie Thiléne : visionneuse plein écran avec navigation sans changer de page */
(function(){
  var box=document.getElementById('galleryLightbox');
  if(!box) return;
  var items=Array.prototype.slice.call(document.querySelectorAll('.detail-gallery-grid .gallery-item'));
  var img=document.getElementById('galleryLightboxImage');
  var counter=document.getElementById('galleryLightboxCounter');
  var text=document.getElementById('galleryLightboxText');
  var current=0;
  function show(index){
    current=(index+items.length)%items.length;
    var source=items[current].querySelector('img');
    img.src=source.currentSrc || source.src;
    img.alt=source.alt || ('Photo '+(current+1));
    counter.textContent=(current+1)+' / '+items.length;
    text.textContent=source.alt || '';
  }
  function open(index){show(index);box.classList.add('open');box.setAttribute('aria-hidden','false');document.body.classList.add('lightbox-open');}
  function close(){box.classList.remove('open');box.setAttribute('aria-hidden','true');document.body.classList.remove('lightbox-open');}
  items.forEach(function(item,index){item.addEventListener('click',function(){open(index);});});
  box.querySelector('.gallery-lightbox-prev').addEventListener('click',function(e){e.stopPropagation();show(current-1);});
  box.querySelector('.gallery-lightbox-next').addEventListener('click',function(e){e.stopPropagation();show(current+1);});
  box.querySelector('.gallery-lightbox-close').addEventListener('click',close);
  box.addEventListener('click',function(e){if(e.target===box) close();});
  document.addEventListener('keydown',function(e){
    if(!box.classList.contains('open')) return;
    if(e.key==='Escape') close();
    if(e.key==='ArrowLeft') show(current-1);
    if(e.key==='ArrowRight') show(current+1);
  });
})();
