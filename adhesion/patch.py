from pathlib import Path
p=Path('/mnt/data/work_adh/adhesion/index.html')
s=p.read_text(encoding='utf-8')
old="""  document.getElementById('submitBtn').addEventListener('click', function(){
    var prenom = document.getElementById('fPrenom').value.trim();
    var nom = document.getElementById('fNom').value.trim();
    var indicatif = codeEl.textContent;
    var tel = document.getElementById('fTel').value.trim();
    var err = document.getElementById('formErr');

    var lieu = '';
    var lieuOk = false;
    if(residenceType === 'senegal'){
      lieu = fCommune.value;
      lieuOk = !!lieu;
    } else {
      var pays = document.getElementById('fPaysAutre').value.trim();
      var ville = document.getElementById('fVilleAutre').value.trim();
      lieuOk = !!(pays && ville);
      lieu = ville && pays ? (ville + ', ' + pays) : '';
    }

    if(!prenom || !nom || !tel || !lieuOk){
      err.classList.add('show');
      err.scrollIntoView({behavior:'smooth', block:'center'});
      return;
    }
    err.classList.remove('show');

    var communeFinale = document.getElementById('fAutreCommune').checked && document.getElementById('fCommuneMilitantisme').value.trim()
      ? document.getElementById('fCommuneMilitantisme').value.trim()
      : lieu;

    document.getElementById('cardName').textContent = (prenom + ' ' + nom).trim() || 'Militant(e)';
    document.getElementById('cardCommune').textContent = communeFinale || '—';
    document.getElementById('cardTel').textContent = indicatif + ' ' + (tel || '—');
    document.getElementById('cardId').textContent = 'DBB-' + Math.floor(10000 + Math.random()*89999);

    document.getElementById('formWrap').style.display = 'none';
    document.getElementById('successWrap').style.display = 'block';
    document.getElementById('successWrap').scrollIntoView({behavior:'smooth', block:'start'});
  });"""
new="""  document.getElementById('submitBtn').addEventListener('click', async function(){
    var btn = this;
    var prenom = document.getElementById('fPrenom').value.trim();
    var nom = document.getElementById('fNom').value.trim();
    var indicatif = codeEl.textContent;
    var tel = document.getElementById('fTel').value.trim();
    var email = document.getElementById('fEmail').value.trim();
    var profession = document.getElementById('fProfession').value.trim();
    var mode = document.getElementById('fMode').value;
    var message = document.getElementById('fMessage').value.trim();
    var err = document.getElementById('formErr');

    var lieu = '';
    var lieuOk = false;
    if(residenceType === 'senegal'){
      lieu = fCommune.value;
      lieuOk = !!lieu;
    } else {
      var pays = document.getElementById('fPaysAutre').value.trim();
      var ville = document.getElementById('fVilleAutre').value.trim();
      lieuOk = !!(pays && ville);
      lieu = ville && pays ? (ville + ', ' + pays) : '';
    }

    if(!prenom || !nom || !tel || !lieuOk){
      err.textContent = 'Merci de renseigner votre prénom, nom, téléphone et lieu de résidence.';
      err.classList.add('show');
      err.scrollIntoView({behavior:'smooth', block:'center'});
      return;
    }
    if(email && !/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email)){
      err.textContent = 'Merci de vérifier votre adresse e-mail.';
      err.classList.add('show');
      err.scrollIntoView({behavior:'smooth', block:'center'});
      return;
    }
    err.classList.remove('show');

    var communeFinale = document.getElementById('fAutreCommune').checked && document.getElementById('fCommuneMilitantisme').value.trim()
      ? document.getElementById('fCommuneMilitantisme').value.trim()
      : lieu;

    btn.disabled = true;
    btn.classList.add('is-loading');
    var originalLabel = btn.innerHTML;
    btn.innerHTML = 'Envoi en cours…';

    try {
      var response = await fetch('https://diamabv.cluster129.hosting.ovh.net/contact.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({
          type: 'adhesion',
          prenom: prenom,
          nom: nom,
          telephone: indicatif + ' ' + tel,
          email: email,
          residence: lieu,
          residence_type: residenceType === 'senegal' ? 'Sénégal' : 'Diaspora',
          commune_militantisme: document.getElementById('fAutreCommune').checked ? document.getElementById('fCommuneMilitantisme').value.trim() : '',
          profession: profession,
          mode_carte: mode,
          message: message
        })
      });
      var result = await response.json();
      if(!response.ok || !result.success) throw new Error(result.message || 'Erreur lors de l’envoi.');

      document.getElementById('cardName').textContent = (prenom + ' ' + nom).trim() || 'Militant(e)';
      document.getElementById('cardCommune').textContent = communeFinale || '—';
      document.getElementById('cardTel').textContent = indicatif + ' ' + (tel || '—');
      document.getElementById('cardId').textContent = 'DBB-' + Math.floor(10000 + Math.random()*89999);

      document.getElementById('formWrap').style.display = 'none';
      document.getElementById('successWrap').style.display = 'block';
      document.getElementById('successWrap').scrollIntoView({behavior:'smooth', block:'start'});
    } catch(e) {
      err.textContent = 'Impossible d’envoyer l’adhésion pour le moment. Vérifiez votre connexion puis réessayez.';
      err.classList.add('show');
      err.scrollIntoView({behavior:'smooth', block:'center'});
      btn.disabled = false;
      btn.classList.remove('is-loading');
      btn.innerHTML = originalLabel;
    }
  });"""
if old not in s:
    raise SystemExit('old block not found')
p.write_text(s.replace(old,new),encoding='utf-8')
