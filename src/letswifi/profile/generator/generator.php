<?php declare(strict_types=1);

/*
 * This file is part of letswifi; a system for easy eduroam device enrollment
 *
 * Copyright: 2018-2022, Jørn Åne de Jong <jorn.dejong@letswifi.eu>
 * Copyright: 2020-2022, Paul Dekkers, SURF <paul.dekkers@surf.nl>
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace letswifi\profile\generator;

use DateTimeInterface;

interface Generator
{
	public function getExpiry(): ?DateTimeInterface;

	public function generate(): string;

	public function getContentType(): string;

	public function getFilename(): string;
}
