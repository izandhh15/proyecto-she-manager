<?php

return [
    // Transfer messages
    'transfer_complete' => 'Â¡Fichaje completado! :player se ha unido a tu plantilla.',
    'transfer_agreed' => ':message El fichaje se completarÃ¡ cuando abra la ventana de :window.',
    'bid_exceeds_budget' => 'La oferta supera tu presupuesto de fichajes.',
    'player_listed' => ':player puesto a la venta. Las ofertas pueden llegar tras la prÃ³xima jornada.',
    'player_unlisted' => ':player retirado de la lista de fichajes.',
    'offer_rejected' => 'Oferta :team_de rechazada.',
    'offer_accepted_sale' => ':player vendido :team_a por :fee.',
    'offer_accepted_pre_contract' => 'Â¡Acuerdo cerrado! :player ficharÃ¡ por :team por :fee cuando abra la ventana de :window.',

    // Free agent signing
    'free_agent_signed' => 'Â¡:player ha fichado por tu equipo como agente libre!',
    'not_free_agent' => 'Este jugador no es agente libre.',
    'transfer_window_closed' => 'La ventana de fichajes estÃ¡ cerrada.',
    'wage_budget_exceeded' => 'Fichar a este jugador superarÃ­a tu presupuesto salarial.',

    // Bid/loan submission confirmations
    'bid_submitted' => 'Tu oferta por :player ha sido enviada. RecibirÃ¡s respuesta prÃ³ximamente.',
    'bid_already_exists' => 'Ya tienes una oferta pendiente por este jugador.',
    'loan_request_submitted' => 'Tu solicitud de cesiÃ³n por :player ha sido enviada. RecibirÃ¡s respuesta prÃ³ximamente.',

    // Counter offer
    'counter_offer_accepted' => 'Â¡Contraoferta aceptada! :player se unirÃ¡ cuando abra la ventana de :window.',
    'counter_offer_accepted_immediate' => 'Â¡Fichaje completado! :player se ha unido a tu plantilla.',
    'counter_offer_expired' => 'Esta oferta ya no estÃ¡ disponible.',

    // Loan messages
    'loan_agreed' => ':message La cesiÃ³n comenzarÃ¡ cuando abra la ventana de :window.',
    'loan_in_complete' => ':message La cesiÃ³n ya estÃ¡ activa.',
    'already_on_loan' => ':player ya estÃ¡ cedido.',
    'loan_search_started' => 'Se ha iniciado la bÃºsqueda de destino para :player. Se te notificarÃ¡ cuando se encuentre un club.',
    'loan_search_active' => ':player ya tiene una bÃºsqueda de cesiÃ³n activa.',

    // Contract messages
    'renewal_agreed' => ':player ha aceptado una extensiÃ³n de :years aÃ±os a :wage/aÃ±o (efectivo desde la prÃ³xima temporada).',
    'renewal_failed' => 'No se pudo procesar la renovaciÃ³n.',
    'renewal_declined' => 'Has decidido no renovar a :player. Se marcharÃ¡ al final de la temporada.',
    'renewal_reconsidered' => 'Has reconsiderado la renovaciÃ³n de :player.',
    'cannot_renew' => 'Este jugador no puede recibir una oferta de renovaciÃ³n.',
    'renewal_offer_submitted' => 'Oferta de renovaciÃ³n enviada a :player por :wage/aÃ±o. Respuesta en la prÃ³xima jornada.',
    'renewal_invalid_offer' => 'La oferta debe ser mayor que cero.',

    // Pre-contract messages
    'pre_contract_accepted' => 'Â¡:player ha aceptado tu oferta de precontrato! Se unirÃ¡ a tu equipo al final de la temporada.',
    'pre_contract_rejected' => ':player ha rechazado tu oferta de precontrato. Intenta mejorar las condiciones salariales.',
    'pre_contract_not_available' => 'Las ofertas de precontrato solo estÃ¡n disponibles entre enero y mayo.',
    'player_not_expiring' => 'Este jugador no tiene el contrato en su Ãºltimo aÃ±o.',
    'pre_contract_submitted' => 'Oferta de precontrato enviada. El jugador responderÃ¡ en los prÃ³ximos dÃ­as.',
    'pre_contract_result_accepted' => 'Â¡:player ha aceptado tu oferta de precontrato!',
    'pre_contract_result_rejected' => ':player ha rechazado tu oferta de precontrato.',

    // Scout messages
    'scout_search_started' => 'El ojeador ha iniciado la bÃºsqueda.',
    'scout_already_searching' => 'Ya tienes una bÃºsqueda activa. CancÃ©lala primero o espera los resultados.',
    'scout_search_cancelled' => 'BÃºsqueda del ojeador cancelada.',
    'scout_search_deleted' => 'BÃºsqueda eliminada.',
    'scout_search_limit' => 'Has alcanzado el lÃ­mite de bÃºsquedas (mÃ¡ximo :max). Elimina una bÃºsqueda antigua para iniciar una nueva.',

    // Shortlist messages
    'shortlist_added' => ':player aÃ±adido a tu lista de seguimiento.',
    'shortlist_removed' => ':player eliminado de tu lista de seguimiento.',
    'shortlist_full' => 'Tu lista de seguimiento estÃ¡ llena (mÃ¡ximo :max jugadores).',

    // Budget messages
    'budget_saved' => 'AsignaciÃ³n de presupuesto guardada.',
    'budget_no_projections' => 'No se encontraron proyecciones financieras.',

    // Season messages
    'budget_exceeds_surplus' => 'La asignaciÃ³n total supera el superÃ¡vit disponible.',
    'budget_minimum_tier' => 'Todas las Ã¡reas de infraestructura deben ser al menos Nivel 1.',

    // Infrastructure upgrades
    'infrastructure_upgraded' => ':area mejorada a Nivel :tier.',
    'infrastructure_upgrade_invalid_area' => 'Ãrea de infraestructura no vÃ¡lida.',
    'infrastructure_upgrade_not_higher' => 'El nivel objetivo debe ser superior al actual.',
    'infrastructure_upgrade_max_tier' => 'El nivel mÃ¡ximo es 4.',
    'infrastructure_upgrade_insufficient_budget' => 'Presupuesto de fichajes insuficiente. La mejora cuesta :cost.',

    // Onboarding
    'welcome_to_team' => 'Â¡Bienvenido :team_a! Tu temporada te espera.',

    // Season
    'season_not_complete' => 'No se puede iniciar una nueva temporada - la temporada actual no ha terminado.',

    // Academy
    'academy_player_promoted' => ':player ha sido subido al primer equipo.',
    'academy_evaluation_required' => 'Debes evaluar a los canteranos antes de continuar.',
    'academy_evaluation_complete' => 'EvaluaciÃ³n de cantera completada.',
    'academy_player_dismissed' => ':player ha sido despedido de la cantera.',
    'academy_player_loaned' => ':player ha sido cedido.',
    'academy_over_capacity' => 'La cantera supera la capacidad. Debes liberar :excess plaza(s).',
    'academy_must_decide_21' => 'Los jugadores de 21+ aÃ±os deben ser subidos o despedidos.',

    // Player release messages
    'player_released' => ':player ha sido liberado. IndemnizaciÃ³n pagada: :severance.',
    'release_not_your_player' => 'Solo puedes liberar jugadores de tu propio equipo.',
    'release_on_loan' => 'No se puede liberar a un jugador cedido.',
    'release_has_agreed_transfer' => 'No se puede liberar a un jugador con un traspaso acordado.',
    'release_has_pre_contract' => 'No se puede liberar a un jugador con un precontrato firmado.',
    'release_squad_too_small' => 'No se puede liberar â€” tu plantilla debe tener al menos :min jugadores.',
    'release_position_minimum' => 'No se puede liberar â€” necesitas al menos :min :group.',

    // Squad cap
    'squad_full' => 'Tu plantilla ya tiene el mÃ¡ximo de :max jugadores. Libera o vende un jugador primero.',
    'squad_trim_required' => 'Tu plantilla tiene :count jugadores. Debes liberar al menos :excess para cumplir el lÃ­mite de :max jugadores.',

    // Pending actions
    'action_required' => 'Hay acciones pendientes que debes resolver antes de continuar.',
    'action_required_short' => 'AcciÃ³n Requerida',

    // Tracking
    'tracking_started' => 'Ahora rastreando a :player.',
    'tracking_stopped' => 'Se dejÃ³ de rastrear a :player.',
    'tracking_slots_full' => 'Todos los seguimientos estÃ¡n en uso. Deja de rastrear a otro jugador primero.',

    // Tactical presets
    'preset_saved' => 'TÃ¡ctica guardada.',
    'preset_updated' => 'TÃ¡ctica actualizada.',
    'preset_deleted' => 'TÃ¡ctica eliminada.',
    'preset_limit_reached' => 'MÃ¡ximo de 3 tÃ¡cticas guardadas alcanzado.',

    // Game management
    'game_deleted' => 'Partida eliminada correctamente.',
    'game_limit_reached' => 'Has alcanzado el lÃ­mite mÃ¡ximo de 3 partidas. Elimina una para crear otra nueva.',

    // Pre-season friendlies
    'pre_season_friendly_requested' => 'Amistoso solicitado contra :team.',
    'pre_season_friendly_cancelled' => 'Amistoso cancelado.',
    'pre_season_friendly_not_found' => 'No se pudo encontrar el amistoso para cancelarlo.',
    'pre_season_invalid_opponent' => 'Rival inválido para un amistoso.',
    'pre_season_foreign_only' => 'En pretemporada solo puedes solicitar amistosos contra equipos extranjeros.',
    'pre_season_no_slots' => 'No hay huecos libres en el calendario de pretemporada.',
    'pre_season_opponent_busy' => 'Ese rival ya tiene partido en esa fecha.',
    'offer_accepted' => 'Oferta aceptada correctamente.',
    'offer_declined' => 'Oferta rechazada.',
    'must_accept_club_offer' => 'Has sido cesado. Debes aceptar una oferta de club para continuar.',
];




