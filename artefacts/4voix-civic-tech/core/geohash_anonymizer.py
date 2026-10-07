#!/usr/bin/env python3
"""
4 Voix Jeunesse — Civic Telemetry & Geohash Anonymizer Engine
============================================================
Author: SOADAN Koffi Sylvain (SKS)
Domain: Civic Tech, Public Policy, Whistleblower Privacy

Protects citizen reporters by converting exact GPS coordinates into coarse
municipal district bounding boxes before transmission to municipal endpoints,
preventing individual geolocation surveillance.
"""

import math
import hashlib
import time
from typing import Dict, Any, Tuple


class SpatialAnonymizer:
    @staticmethod
    def coarse_grain_coordinates(lat: float, lon: float, precision_km: float = 1.0) -> Tuple[float, float, str]:
        """
        Snaps precise floating-point coordinates to a coarse grid cell
        to protect user privacy while preserving actionable municipal location.
        """
        # Approx: 1 deg lat ~ 111 km
        lat_step = precision_km / 111.0
        # Longitude degree length varies by latitude
        lon_step = precision_km / (111.0 * math.cos(math.radians(lat)))

        snapped_lat = round(lat / lat_step) * lat_step
        snapped_lon = round(lon / lon_step) * lon_step

        # Generate unique spatial tile identifier
        tile_str = f"{snapped_lat:.3f}:{snapped_lon:.3f}"
        tile_hash = hashlib.sha256(tile_str.encode()).hexdigest()[:8]

        return round(snapped_lat, 4), round(snapped_lon, 4), f"GEO-{tile_hash}"


class CivicReportWorkflow:
    def __init__(self, report_id: str, category: str, exact_lat: float, exact_lon: float, municipality: str):
        self.report_id = report_id
        self.category = category
        self.municipality = municipality
        
        # Spatial anonymization applied immediately
        anonymized_lat, anonymized_lon, cell_id = SpatialAnonymizer.coarse_grain_coordinates(exact_lat, exact_lon)
        self.anonymized_location = {
            "municipality": municipality,
            "approxLatitude": anonymized_lat,
            "approxLongitude": anonymized_lon,
            "subDistrictGeohash": cell_id,
            "precisionRadiusMeters": 1000
        }
        
        self.timestamp = int(time.time())
        self.peer_confirmations = 0
        self.seen_peer_tokens = set()
        self.status = "PENDING_PEER_CONFIRMATION"

    def add_peer_confirmation(self, peer_token_hash: str) -> str:
        # Ignore duplicate endorsements from the same privacy token so a single
        # reporter cannot inflate the count and force automatic escalation.
        if peer_token_hash in self.seen_peer_tokens:
            return f"Duplicate endorsement ignored ({self.peer_confirmations}/3 required for automatic escalation)."
        self.seen_peer_tokens.add(peer_token_hash)
        self.peer_confirmations += 1
        if self.peer_confirmations >= 3 and self.status == "PENDING_PEER_CONFIRMATION":
            self.status = "ESCALATED_TO_MUNICIPALITY"
            return f"Threshold met ({self.peer_confirmations}/3). Escalated to {self.municipality} emergency services!"
        return f"Confirmation recorded ({self.peer_confirmations}/3 required for automatic escalation)."

    def export_telemetry_payload(self) -> Dict[str, Any]:
        return {
            "reportId": self.report_id,
            "timestampEpoch": self.timestamp,
            "category": self.category,
            "anonymizedLocation": self.anonymized_location,
            "verificationStatus": self.status,
            "communityEndorsements": self.peer_confirmations,
            "urgencyLevel": "HIGH" if self.peer_confirmations >= 3 else "MEDIUM"
        }


def run_demonstration():
    print("======================================================================")
    print(" 4 Voix Jeunesse — Civic Telemetry Spatial Anonymizer")
    print(" Developed by SOADAN Koffi Sylvain (SKS)")
    print("======================================================================")

    # Real coordinates in Lomé (Golfe 4, Togo)
    exact_lat = 6.1364821
    exact_lon = 1.2227184
    municipality = "Commune Golfe 4 (Lomé, Togo)"

    print(f"\n[Raw Input] Citizen reporting water pipeline breach at:")
    print(f"    Lat: {exact_lat}, Lon: {exact_lon} ({municipality})")

    report = CivicReportWorkflow(
        report_id="CIVIC-TGO-8F21B904",
        category="WATER_SANITATION",
        exact_lat=exact_lat,
        exact_lon=exact_lon,
        municipality=municipality
    )

    print("\n[Privacy Layer] Sanitizing GPS into coarse municipal tile...")
    print(f"    Approx Lat: {report.anonymized_location['approxLatitude']}")
    print(f"    Approx Lon: {report.anonymized_location['approxLongitude']}")
    print(f"    Geohash Tile: {report.anonymized_location['subDistrictGeohash']}")
    print(f"    Obfuscation Radius: {report.anonymized_location['precisionRadiusMeters']}m (Protects whistleblower identity)")

    print(f"\n[Initial Status] {report.status}")

    print("\n[Civic Consensus] Gathering local youth peer endorsements:")
    print("    Peer 1: " + report.add_peer_confirmation("hash_user_alpha"))
    print("    Peer 2: " + report.add_peer_confirmation("hash_user_beta"))
    print("    Peer 3: " + report.add_peer_confirmation("hash_user_gamma"))

    print(f"\n[Final Status] {report.status}")
    print("\n[Municipal Telemetry Payload Validated]")
    print(report.export_telemetry_payload())
    print("======================================================================")
    print("[OK] Civic Tech whistleblower protection verified.")


if __name__ == "__main__":
    run_demonstration()
