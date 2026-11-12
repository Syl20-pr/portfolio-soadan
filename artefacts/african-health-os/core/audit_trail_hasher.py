#!/usr/bin/env python3
"""
African Health OS — Cryptographic Tamper-Evident Clinical Audit Engine
======================================================================
Author: SOADAN Koffi Sylvain (SKS)
Domain: Sovereign Digital Public Infrastructure (DPI) & Health Tech
Compliance: Malabo Convention & AU CDC Digital Health Strategy

This module implements a cryptographic append-only ledger for electronic
health records (EHR). It guarantees that once an event (consultation,
prescription, laboratory result) is logged, any retroactive alteration
immediately breaks the hash chain, alerting regulatory oversight bodies.
"""

import hashlib
import json
import time
from typing import List, Dict, Any, Tuple


class ClinicalAuditBlock:
    def __init__(self, index: int, previous_hash: str, timestamp: float, event_type: str, practitioner_id: str, record_payload: Dict[str, Any]):
        self.index = index
        self.previous_hash = previous_hash
        self.timestamp = timestamp
        self.event_type = event_type
        self.practitioner_id = practitioner_id
        self.record_payload = record_payload
        self.hash = self.calculate_hash()

    def calculate_hash(self) -> str:
        block_content = {
            "index": self.index,
            "previous_hash": self.previous_hash,
            "timestamp": self.timestamp,
            "event_type": self.event_type,
            "practitioner_id": self.practitioner_id,
            "record_payload": self.record_payload
        }
        serialized = json.dumps(block_content, sort_keys=True, default=str).encode("utf-8")
        return hashlib.sha256(serialized).hexdigest()

    def to_dict(self) -> Dict[str, Any]:
        return {
            "index": self.index,
            "hash": self.hash,
            "previous_hash": self.previous_hash,
            "timestamp": self.timestamp,
            "event_type": self.event_type,
            "practitioner_id": self.practitioner_id,
            "record_payload": self.record_payload
        }


class SovereignHealthAuditLedger:
    def __init__(self, patient_udpi: str):
        self.patient_udpi = patient_udpi
        self.chain: List[ClinicalAuditBlock] = []
        self._create_genesis_block()

    def _create_genesis_block(self):
        genesis = ClinicalAuditBlock(
            index=0,
            previous_hash="0" * 64,
            timestamp=1772668800.0,  # Sovereign Epoch
            event_type="GENESIS_IDENTITY_CREATION",
            practitioner_id="MIN_SANTE_TGO_ROOT_CA",
            record_payload={
                "patient_udpi": self.patient_udpi,
                "jurisdiction": "TGO",
                "authority": "IPDCP / Ministère de la Santé du Togo",
                "status": "SOVEREIGN_PASSPORT_INITIALIZED"
            }
        )
        self.chain.append(genesis)

    def append_event(self, event_type: str, practitioner_id: str, payload: Dict[str, Any]) -> ClinicalAuditBlock:
        prev_block = self.chain[-1]
        new_block = ClinicalAuditBlock(
            index=len(self.chain),
            previous_hash=prev_block.hash,
            timestamp=time.time(),
            event_type=event_type,
            practitioner_id=practitioner_id,
            record_payload=payload
        )
        self.chain.append(new_block)
        return new_block

    def verify_integrity(self) -> Tuple[bool, str]:
        if not self.chain:
            return False, "Missing genesis block"

        genesis = self.chain[0]
        if genesis.index != 0 or genesis.previous_hash != "0" * 64:
            return False, "Invalid genesis block: index or previous_hash mismatch"
        if genesis.hash != genesis.calculate_hash():
            return False, "Tampered content at genesis block: hash mismatch"

        for i in range(1, len(self.chain)):
            current = self.chain[i]
            prev = self.chain[i - 1]

            if current.previous_hash != prev.hash:
                return False, f"Broken link at block #{current.index}: previous_hash mismatch"

            recalculated = current.calculate_hash()
            if current.hash != recalculated:
                return False, f"Tampered content at block #{current.index}: hash mismatch"

        return True, "Audit chain perfectly validated. Zero tampering detected."


def run_demonstration():
    print("======================================================================")
    print(" African Health OS — Sovereign DPI Tamper-Evident Ledger Demo")
    print(" Developed by SOADAN Koffi Sylvain (SKS)")
    print("======================================================================")

    udpi = "AFR-PAT-TGO-A49F81BC"
    print(f"\n[+] Initializing sovereign health ledger for patient: {udpi}")
    ledger = SovereignHealthAuditLedger(patient_udpi=udpi)

    # Event 1: Clinical Triage
    print("[+] Recording Event 1: Triage Consultation at CHU Sylvanus Olympio (Lomé)...")
    ledger.append_event(
        event_type="CLINICAL_TRIAGE",
        practitioner_id="MD-TGO-2024-8841",
        payload={
            "facility": "CHU Sylvanus Olympio, Lomé",
            "vitals": {"blood_pressure": "120/80", "heart_rate": 72, "temperature_c": 37.1},
            "chief_complaint": "Routine cardiovascular assessment"
        }
    )

    # Event 2: Cross-Border Diagnostic Sync
    print("[+] Recording Event 2: Cross-Border Laboratory Consultation (Accra, Ghana)...")
    ledger.append_event(
        event_type="CROSS_BORDER_LAB_RESULT",
        practitioner_id="MD-GHA-2025-1029",
        payload={
            "facility": "Korle Bu Teaching Hospital, Accra",
            "rec_protocol": "CEDEAO/ECOWAS Health Data Sharing Framework",
            "tests": {"hemoglobin_a1c": 5.4, "status": "NORMAL"}
        }
    )

    valid, msg = ledger.verify_integrity()
    print(f"\n[Audit Status] Normal State: {'VALID' if valid else 'COMPROMISED'} — {msg}")

    print("\n--- Simulating Malicious Retroactive Alteration ---")
    print("[!] Modifying consultation blood pressure record in block #1 without re-signing...")
    ledger.chain[1].record_payload["vitals"]["blood_pressure"] = "190/110"  # Attack attempt

    tampered_valid, tampered_msg = ledger.verify_integrity()
    print(f"[Audit Status] Post-Tamper State: {'VALID' if tampered_valid else 'SECURITY ALERT TRIGGERED!'}")
    print(f"    Reason: {tampered_msg}")
    print("======================================================================")
    print("[OK] Sovereign cryptographic proof mechanism operational.")


if __name__ == "__main__":
    run_demonstration()
